<?php

declare(strict_types=1);

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nativesitemap
 */

namespace OrkaCS\Component\Nativesitemap\Administrator\Service;

defined('_JEXEC') or die;

final class DuplicateRemover
{
    /**
     * Remove duplicate sitemap entries while preserving the best metadata.
     *
     * Two entries are duplicates when their absolute locations are identical.
     * When only one occurrence has a last modification date, that occurrence
     * is retained.
     *
     * @param   SitemapUrl[]  $urls  Sitemap entries to filter.
     *
     * @return  SitemapUrl[]
     */
    public function remove(array $urls): array
    {
        $indexes = [];
        $uniqueUrls = [];

        foreach ($urls as $url) {
            $location = $url->getLocation();

            if (!isset($indexes[$location])) {
                $indexes[$location] = count($uniqueUrls);
                $uniqueUrls[] = $url;
                continue;
            }

            $index = $indexes[$location];
            $existingUrl = $uniqueUrls[$index];

            if (
                $existingUrl->getLastModified() === null
                && $url->getLastModified() !== null
            ) {
                $uniqueUrls[$index] = $url;
            }
        }

        return $uniqueUrls;
    }
}
