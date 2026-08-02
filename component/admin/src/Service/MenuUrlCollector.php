<?php

declare(strict_types=1);

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nativesitemap
 */

namespace OrkaCS\Component\Nativesitemap\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use JsonException;
use stdClass;

/**
 * Collecte les pages publiées par les menus du site Joomla.
 *
 * Responsabilités :
 * - sélectionner les éléments de menu du site publiés et publics ;
 * - conserver uniquement les liens vers un composant Joomla ;
 * - exclure les éléments déclarés en noindex ;
 * - construire des URL SEF absolues à partir de leur Itemid.
 *
 * Ne fait pas :
 * - la collecte des articles ou des catégories ;
 * - la génération XML ;
 * - l'écriture du fichier sitemap.xml ;
 * - la déduplication globale des URL.
 */
final class MenuUrlCollector
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
        $items = $this->loadMenuItems();
        $urls  = [];

        foreach ($items as $item) {
            if ($this->isNoIndex((string) $item->params)) {
                continue;
            }

            $location = Route::link(
                'site',
                'index.php?Itemid=' . (int) $item->id,
                true,
                Route::TLS_IGNORE,
                true
            );

            $urls[] = new SitemapUrl($location);
        }

        return $urls;
    }

    /**
     * @return stdClass[]
     */
    private function loadMenuItems(): array
    {
        $guestViewLevels = Access::getAuthorisedViewLevels(0);

        if ($guestViewLevels === []) {
            return [];
        }

        $menuItemType = 'component';

        $query = $this->database->getQuery(true)
            ->select([
                $this->database->quoteName('m.id'),
                $this->database->quoteName('m.params'),
            ])
            ->from($this->database->quoteName('#__menu', 'm'))
            ->where($this->database->quoteName('m.client_id') . ' = 0')
            ->where($this->database->quoteName('m.published') . ' = 1')
            ->where($this->database->quoteName('m.type') . ' = :menuItemType')
            ->whereIn($this->database->quoteName('m.access'), $guestViewLevels, ParameterType::INTEGER)
            ->bind(':menuItemType', $menuItemType, ParameterType::STRING)
            ->order($this->database->quoteName('m.lft') . ' ASC');

        return $this->database->setQuery($query)->loadObjectList();
    }

    private function isNoIndex(string $params): bool
    {
        if ($params === '') {
            return false;
        }

        try {
            $values = json_decode($params, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        if (!is_array($values)) {
            return false;
        }

        $robots = strtolower(trim((string) ($values['robots'] ?? '')));

        return str_contains($robots, 'noindex');
    }
}
