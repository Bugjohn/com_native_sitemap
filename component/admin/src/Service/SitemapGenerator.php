<?php

declare(strict_types=1);

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nativesitemap
 */

namespace OrkaCS\Component\Nativesitemap\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class SitemapGenerator
{
    public function __construct(
        private readonly DatabaseInterface $database
    ) {
    }

    /**
     * Collect, merge and deduplicate all sitemap URLs, then generate the XML.
     *
     * @return array{
     *     articles: int,
     *     categories: int,
     *     menus: int,
     *     rawTotal: int,
     *     duplicates: int,
     *     finalTotal: int,
     *     articleUrls: SitemapUrl[],
     *     categoryUrls: SitemapUrl[],
     *     menuUrls: SitemapUrl[],
     *     urls: SitemapUrl[],
     *     xml: string
     * }
     */
    public function generate(): array
    {
        $articleUrls = (new ArticleUrlCollector($this->database))->collect();
        $categoryUrls = (new CategoryUrlCollector($this->database))->collect();
        $menuUrls = (new MenuUrlCollector($this->database))->collect();

        $allUrls = array_merge($articleUrls, $categoryUrls, $menuUrls);
        $uniqueUrls = (new DuplicateRemover())->remove($allUrls);

        usort(
            $uniqueUrls,
            static fn (SitemapUrl $first, SitemapUrl $second): int =>
                strcmp($first->getLocation(), $second->getLocation())
        );

        $xml = (new XmlSitemapGenerator())->generate($uniqueUrls);

        $rawTotal = count($allUrls);
        $finalTotal = count($uniqueUrls);

        return [
            'articles' => count($articleUrls),
            'categories' => count($categoryUrls),
            'menus' => count($menuUrls),
            'rawTotal' => $rawTotal,
            'duplicates' => $rawTotal - $finalTotal,
            'finalTotal' => $finalTotal,
            'articleUrls' => $articleUrls,
            'categoryUrls' => $categoryUrls,
            'menuUrls' => $menuUrls,
            'urls' => $uniqueUrls,
            'xml' => $xml,
        ];
    }
}
