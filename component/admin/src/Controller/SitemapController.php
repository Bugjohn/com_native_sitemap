<?php

declare(strict_types=1);

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nativesitemap
 */

namespace OrkaCS\Component\Nativesitemap\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use OrkaCS\Component\Nativesitemap\Administrator\Service\SitemapGenerator;
use OrkaCS\Component\Nativesitemap\Administrator\Service\SitemapWriter;
use Throwable;

final class SitemapController extends BaseController
{
    public function generate(): void
    {
        $this->checkToken();

        try {
            $database = Factory::getContainer()->get(DatabaseInterface::class);
            $result = (new SitemapGenerator($database))->generate();
            $writtenBytes = (new SitemapWriter())->write($result['xml']);

            Factory::getApplication()->setUserState(
                'com_nativesitemap.sitemap',
                [
                    'path' => JPATH_ROOT . '/sitemap.xml',
                    'url' => Uri::root() . 'sitemap.xml',
                    'size' => $writtenBytes,
                    'urls' => $result['finalTotal'],
                    'generatedAt' => Factory::getDate()->format('Y-m-d H:i:s'),
                ]
            );

            $this->setRedirect(
                Route::_('index.php?option=com_nativesitemap', false),
                Text::sprintf(
                    'COM_NATIVESITEMAP_GENERATION_SUCCESS',
                    $result['finalTotal']
                ),
                'success'
            );
        } catch (Throwable $exception) {
            $this->setRedirect(
                Route::_('index.php?option=com_nativesitemap', false),
                Text::sprintf(
                    'COM_NATIVESITEMAP_GENERATION_ERROR',
                    $exception->getMessage()
                ),
                'error'
            );
        }
    }
}
