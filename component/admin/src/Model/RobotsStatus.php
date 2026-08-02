<?php

declare(strict_types=1);

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nativesitemap
 */

namespace OrkaCS\Component\Nativesitemap\Administrator\Model;

defined('_JEXEC') or die;

final class RobotsStatus
{
    public function __construct(
        private readonly string $path,
        private readonly string $expectedSitemapUrl,
        private readonly bool $exists,
        private readonly bool $writable,
        private readonly int $sitemapDirectiveCount,
        private readonly ?string $currentSitemapUrl,
        private readonly bool $synchronized,
        private readonly ?string $content,
        private readonly ?string $backupPath = null
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getExpectedSitemapUrl(): string
    {
        return $this->expectedSitemapUrl;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function getSitemapDirectiveCount(): int
    {
        return $this->sitemapDirectiveCount;
    }

    public function getCurrentSitemapUrl(): ?string
    {
        return $this->currentSitemapUrl;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getBackupPath(): ?string
    {
        return $this->backupPath;
    }
}
