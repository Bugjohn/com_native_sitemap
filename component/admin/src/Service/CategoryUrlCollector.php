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
 * Collecte les catégories d'articles Joomla publiées et indexables.
 *
 * Responsabilités :
 * - sélectionner les catégories du composant de contenu ;
 * - conserver uniquement les catégories publiées et publiques ;
 * - exclure la catégorie racine et les catégories déclarées en noindex ;
 * - construire des URL SEF absolues avec leur date de modification.
 *
 * Ne fait pas :
 * - la collecte des articles ;
 * - la génération XML ;
 * - l'écriture du fichier sitemap.xml ;
 * - la déduplication globale des URL.
 */
final class CategoryUrlCollector
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
        $categories = $this->loadCategories();
        $urls       = [];

        foreach ($categories as $category) {
            if ($this->isNoIndex((string) $category->metadata)) {
                continue;
            }

            $route = RouteHelper::getCategoryRoute(
                (int) $category->id,
                (string) $category->language
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
                $this->createLastModifiedDate($category)
            );
        }

        return $urls;
    }

    /**
     * @return stdClass[]
     */
    private function loadCategories(): array
    {
        $guestViewLevels = Access::getAuthorisedViewLevels(0);

        if ($guestViewLevels === []) {
            return [];
        }

        $extension = 'com_content';

        $query = $this->database->getQuery(true)
            ->select([
                $this->database->quoteName('c.id'),
                $this->database->quoteName('c.language'),
                $this->database->quoteName('c.metadata'),
                $this->database->quoteName('c.created_time'),
                $this->database->quoteName('c.modified_time'),
            ])
            ->from($this->database->quoteName('#__categories', 'c'))
            ->where($this->database->quoteName('c.extension') . ' = :extension')
            ->where($this->database->quoteName('c.published') . ' = 1')
            ->where($this->database->quoteName('c.id') . ' > 1')
            ->whereIn($this->database->quoteName('c.access'), $guestViewLevels, ParameterType::INTEGER)
            ->bind(':extension', $extension, ParameterType::STRING)
            ->order($this->database->quoteName('c.lft') . ' ASC');

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

    private function createLastModifiedDate(stdClass $category): DateTimeImmutable
    {
        $value = (string) $category->modified_time;

        if ($value === '' || $value === $this->database->getNullDate()) {
            $value = (string) $category->created_time;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (\Exception $exception) {
            throw new RuntimeException(
                sprintf('Impossible de lire la date de la catégorie %d.', (int) $category->id),
                0,
                $exception
            );
        }
    }
}
