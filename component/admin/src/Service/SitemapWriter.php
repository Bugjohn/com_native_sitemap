<?php

declare(strict_types=1);

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nativesitemap
 */

namespace OrkaCS\Component\Nativesitemap\Administrator\Service;

defined('_JEXEC') or die;

use RuntimeException;

final class SitemapWriter
{
    /**
     * Write the generated XML to sitemap.xml at the Joomla site root.
     *
     * @return int Number of bytes written.
     */
    public function write(string $xml): int
    {
        if ($xml === '') {
            throw new RuntimeException('The sitemap XML cannot be empty.');
        }

        $path = JPATH_ROOT . '/sitemap.xml';

        if (is_file($path) && !is_writable($path)) {
            throw new RuntimeException('The existing sitemap.xml file is not writable.');
        }

        if (!is_file($path) && !is_writable(JPATH_ROOT)) {
            throw new RuntimeException('The Joomla site root is not writable.');
        }

        $writtenBytes = file_put_contents($path, $xml, LOCK_EX);

        if ($writtenBytes === false) {
            throw new RuntimeException('Unable to write sitemap.xml at the Joomla site root.');
        }

        return $writtenBytes;
    }
}
