<?php

declare(strict_types=1);

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nativesitemap
 */

namespace OrkaCS\Component\Nativesitemap\Administrator\Service;

defined('_JEXEC') or die;

use OrkaCS\Component\Nativesitemap\Administrator\Model\RobotsStatus;
use RuntimeException;

final class RobotsManager
{
    private const SITEMAP_DIRECTIVE_PATTERN = '/^\s*Sitemap\s*:\s*(.*?)\s*$/i';

    public function __construct(
        private readonly string $sitemapUrl
    ) {
        if (trim($this->sitemapUrl) === '') {
            throw new RuntimeException('The sitemap URL cannot be empty.');
        }
    }

    public function analyse(): RobotsStatus
    {
        $path = $this->getRobotsPath();
        $exists = is_file($path);
        $writable = $exists ? is_writable($path) : is_writable(JPATH_ROOT);

        if (!$exists) {
            return new RobotsStatus(
                $path,
                $this->sitemapUrl,
                false,
                $writable,
                0,
                null,
                false,
                null
            );
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException('Unable to read robots.txt.');
        }

        $directives = $this->findSitemapDirectives($content);
        $currentUrl = $directives[0] ?? null;
        $synchronized = count($directives) === 1
            && $currentUrl === $this->sitemapUrl;

        return new RobotsStatus(
            $path,
            $this->sitemapUrl,
            true,
            $writable,
            count($directives),
            $currentUrl,
            $synchronized,
            $content
        );
    }

    public function synchronize(): RobotsStatus
    {
        $status = $this->analyse();

        if ($status->isSynchronized()) {
            return $status;
        }

        if (!$status->isWritable()) {
            throw new RuntimeException('robots.txt or the Joomla site root is not writable.');
        }

        $path = $status->getPath();
        $backupPath = null;
        $content = '';

        if ($status->exists()) {
            $content = file_get_contents($path);

            if ($content === false) {
                throw new RuntimeException('Unable to read robots.txt before update.');
            }

            $backupPath = $this->createBackup($path, $content);
        }

        $updatedContent = $this->replaceSitemapDirective($content);
        $writtenBytes = file_put_contents($path, $updatedContent, LOCK_EX);

        if ($writtenBytes === false) {
            throw new RuntimeException('Unable to write robots.txt.');
        }

        $updatedStatus = $this->analyse();

        if (!$updatedStatus->isSynchronized()) {
            throw new RuntimeException('robots.txt was written but the Sitemap directive is not synchronized.');
        }

        return new RobotsStatus(
            $updatedStatus->getPath(),
            $updatedStatus->getExpectedSitemapUrl(),
            $updatedStatus->exists(),
            $updatedStatus->isWritable(),
            $updatedStatus->getSitemapDirectiveCount(),
            $updatedStatus->getCurrentSitemapUrl(),
            $updatedStatus->isSynchronized(),
            $updatedStatus->getContent(),
            $backupPath
        );
    }

    private function getRobotsPath(): string
    {
        return JPATH_ROOT . '/robots.txt';
    }

    /**
     * @return string[]
     */
    private function findSitemapDirectives(string $content): array
    {
        $directives = [];
        $lines = preg_split('/\R/', $content) ?: [];

        foreach ($lines as $line) {
            if (preg_match(self::SITEMAP_DIRECTIVE_PATTERN, $line, $matches) === 1) {
                $directives[] = trim($matches[1]);
            }
        }

        return $directives;
    }

    private function replaceSitemapDirective(string $content): string
    {
        $normalizedContent = str_replace(["\r\n", "\r"], "\n", $content);
        $hadFinalNewline = $normalizedContent !== '' && str_ends_with($normalizedContent, "\n");
        $lines = $normalizedContent === '' ? [] : explode("\n", rtrim($normalizedContent, "\n"));
        $updatedLines = [];
        $directiveInserted = false;

        foreach ($lines as $line) {
            if (preg_match(self::SITEMAP_DIRECTIVE_PATTERN, $line) === 1) {
                if (!$directiveInserted) {
                    $updatedLines[] = 'Sitemap: ' . $this->sitemapUrl;
                    $directiveInserted = true;
                }

                continue;
            }

            $updatedLines[] = $line;
        }

        if (!$directiveInserted) {
            if ($updatedLines !== [] && end($updatedLines) !== '') {
                $updatedLines[] = '';
            }

            $updatedLines[] = 'Sitemap: ' . $this->sitemapUrl;
        }

        $updatedContent = implode("\n", $updatedLines);

        if ($content === '' || $hadFinalNewline || !$directiveInserted) {
            $updatedContent .= "\n";
        }

        return $updatedContent;
    }

    private function createBackup(string $path, string $content): string
    {
        $backupPath = sprintf(
            '%s.backup-%s',
            $path,
            date('Ymd-His')
        );

        $suffix = 1;

        while (file_exists($backupPath)) {
            $backupPath = sprintf(
                '%s.backup-%s-%d',
                $path,
                date('Ymd-His'),
                $suffix
            );
            $suffix++;
        }

        $writtenBytes = file_put_contents($backupPath, $content, LOCK_EX);

        if ($writtenBytes === false) {
            throw new RuntimeException('Unable to create the robots.txt backup.');
        }

        return $backupPath;
    }
}
