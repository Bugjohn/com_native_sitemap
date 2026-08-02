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
use Joomla\Database\DatabaseInterface;
use OrkaCS\Component\Nativesitemap\Administrator\Service\SitemapGenerator;
use Throwable;

final class DiagnosticController extends BaseController
{
    public function run(): void
    {
        $this->checkToken();

        try {
            $database = Factory::getContainer()->get(DatabaseInterface::class);
            $result = (new SitemapGenerator($database))->generate();

            $diagnostic = [
                'articles' => $result['articles'],
                'categories' => $result['categories'],
                'menus' => $result['menus'],
                'rawTotal' => $result['rawTotal'],
                'duplicates' => $result['duplicates'],
                'finalTotal' => $result['finalTotal'],
                'articleUrls' => array_map(
                    static fn ($url): string => $url->getLocation(),
                    array_slice($result['articleUrls'], 0, 20)
                ),
                'categoryUrls' => array_map(
                    static fn ($url): string => $url->getLocation(),
                    array_slice($result['categoryUrls'], 0, 20)
                ),
                'menuUrls' => array_map(
                    static fn ($url): string => $url->getLocation(),
                    array_slice($result['menuUrls'], 0, 20)
                ),
                'finalUrls' => array_map(
                    static fn ($url): string => $url->getLocation(),
                    array_slice($result['urls'], 0, 30)
                ),
                'xmlPreview' => implode("\n", array_slice(explode("\n", $result['xml']), 0, 40)),
            ];

            Factory::getApplication()->setUserState(
                'com_nativesitemap.diagnostic',
                $diagnostic
            );

            $this->setRedirect(
                Route::_('index.php?option=com_nativesitemap', false),
                Text::sprintf(
                    'COM_NATIVESITEMAP_DIAGNOSTIC_SUCCESS',
                    $diagnostic['articles'],
                    $diagnostic['categories'],
                    $diagnostic['menus']
                ),
                'success'
            );
        } catch (Throwable $exception) {
            Factory::getApplication()->setUserState(
                'com_nativesitemap.diagnostic',
                null
            );

            $this->setRedirect(
                Route::_('index.php?option=com_nativesitemap', false),
                Text::sprintf(
                    'COM_NATIVESITEMAP_DIAGNOSTIC_ERROR',
                    $exception->getMessage()
                ),
                'error'
            );
        }
    }
}
