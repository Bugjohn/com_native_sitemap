<?php

declare(strict_types=1);

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nativesitemap
 */

namespace OrkaCS\Component\Nativesitemap\Administrator\Service;

defined('_JEXEC') or die;

use InvalidArgumentException;

final class XmlSitemapGenerator
{
    private const XML_NAMESPACE = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    /**
     * @param SitemapUrl[] $urls
     */
    public function generate(array $urls): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            sprintf('<urlset xmlns="%s">', self::XML_NAMESPACE),
        ];

        foreach ($urls as $url) {
            if (!$url instanceof SitemapUrl) {
                throw new InvalidArgumentException(
                    'The sitemap generator accepts only SitemapUrl objects.'
                );
            }

            $lines[] = '  <url>';
            $lines[] = sprintf(
                '    <loc>%s</loc>',
                $this->escape($url->getLocation())
            );

            if ($url->getLastModified() !== null) {
                $lines[] = sprintf(
                    '    <lastmod>%s</lastmod>',
                    $url->getLastModified()->format('Y-m-d')
                );
            }

            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines) . "\n";
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
