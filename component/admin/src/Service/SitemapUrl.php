<?php

declare(strict_types=1);

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nativesitemap
 */

namespace OrkaCS\Component\Nativesitemap\Administrator\Service;

defined('_JEXEC') or die;

use DateTimeImmutable;
use InvalidArgumentException;

final class SitemapUrl
{
    public function __construct(
        private readonly string $location,
        private readonly ?DateTimeImmutable $lastModified = null
    ) {
        if (filter_var($this->location, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('The sitemap URL is invalid.');
        }

        $scheme = strtolower((string) parse_url($this->location, PHP_URL_SCHEME));

        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('The sitemap URL must use HTTP or HTTPS.');
        }
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getLastModified(): ?DateTimeImmutable
    {
        return $this->lastModified;
    }
}
