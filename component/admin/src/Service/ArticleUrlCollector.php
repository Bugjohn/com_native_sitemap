<?php

declare(strict_types=1);

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nativesitemap
 */

namespace OrkaCS\Component\Nativesitemap\Administrator\Service;

defined('_JEXEC') or die;

use DateTimeImmutable;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use JsonException;
use RuntimeException;
use stdClass;

/**
 * Collecte les articles Joomla publiés et indexables.
 *
 * Responsabilités :
 * - sélectionner les articles accessibles aux visiteurs anonymes ;
 * - respecter les dates de publication ;
 * - exclure les articles et catégories non publiés ;
 * - exclure les articles déclarés en noindex ;
 * - construire des URL SEF absolues avec leur date de modification.
 *
 * Ne fait pas :
 * - la génération XML ;
 * - l'écriture du fichier sitemap.xml ;
 * - la déduplication globale des URL.
 */
final class ArticleUrlCollector
{
    public function __construct(
        private readonly DatabaseInterface $database
    ) {
    }

    /**
     * @return SitemapUrl[]
     */
    public function collect(): array
    {
        $articles = $this->loadArticles();
        $urls     = [];

        foreach ($articles as $article) {
            if ($this->isNoIndex((string) $article->metadata)) {
                continue;
            }

            $route = RouteHelper::getArticleRoute(
                (int) $article->id,
                (int) $article->catid,
                (string) $article->language
            );

            $location = Route::link(
                'site',
                $route,
                true,
                Route::TLS_IGNORE,
                true
            );

            $urls[] = new SitemapUrl(
                $location,
                $this->createLastModifiedDate($article)
            );
        }

        return $urls;
    }

    /**
     * @return stdClass[]
     */
    private function loadArticles(): array
    {
        $now              = $this->database->quote((new DateTimeImmutable())->format('Y-m-d H:i:s'));
        $nullDate         = $this->database->quote($this->database->getNullDate());
        $guestViewLevels  = Access::getAuthorisedViewLevels(0);

        if ($guestViewLevels === []) {
            return [];
        }

        $query = $this->database->getQuery(true)
            ->select([
                $this->database->quoteName('a.id'),
                $this->database->quoteName('a.catid'),
                $this->database->quoteName('a.language'),
                $this->database->quoteName('a.metadata'),
                $this->database->quoteName('a.created'),
                $this->database->quoteName('a.modified'),
            ])
            ->from($this->database->quoteName('#__content', 'a'))
            ->innerJoin(
                $this->database->quoteName('#__categories', 'c')
                . ' ON ' . $this->database->quoteName('c.id')
                . ' = ' . $this->database->quoteName('a.catid')
            )
            ->where($this->database->quoteName('a.state') . ' = 1')
            ->where($this->database->quoteName('c.published') . ' = 1')
            ->whereIn($this->database->quoteName('a.access'), $guestViewLevels, ParameterType::INTEGER)
            ->whereIn($this->database->quoteName('c.access'), $guestViewLevels, ParameterType::INTEGER)
            ->where(
                '(' . $this->database->quoteName('a.publish_up') . ' IS NULL'
                . ' OR ' . $this->database->quoteName('a.publish_up') . ' = ' . $nullDate
                . ' OR ' . $this->database->quoteName('a.publish_up') . ' <= ' . $now . ')'
            )
            ->where(
                '(' . $this->database->quoteName('a.publish_down') . ' IS NULL'
                . ' OR ' . $this->database->quoteName('a.publish_down') . ' = ' . $nullDate
                . ' OR ' . $this->database->quoteName('a.publish_down') . ' >= ' . $now . ')'
            )
            ->order($this->database->quoteName('a.id') . ' ASC');

        return $this->database->setQuery($query)->loadObjectList();
    }

    private function isNoIndex(string $metadata): bool
    {
        if ($metadata === '') {
            return false;
        }

        try {
            $values = json_decode($metadata, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        if (!is_array($values)) {
            return false;
        }

        $robots = strtolower(trim((string) ($values['robots'] ?? '')));

        return str_contains($robots, 'noindex');
    }

    private function createLastModifiedDate(stdClass $article): DateTimeImmutable
    {
        $value = (string) $article->modified;

        if ($value === '' || $value === $this->database->getNullDate()) {
            $value = (string) $article->created;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (\Exception $exception) {
            throw new RuntimeException(
                sprintf('Impossible de lire la date de l’article %d.', (int) $article->id),
                0,
                $exception
            );
        }
    }
}
