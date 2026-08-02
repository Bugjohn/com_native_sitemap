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
use OrkaCS\Component\Nativesitemap\Administrator\Service\RobotsManager;
use Throwable;

final class RobotsController extends BaseController
{
    public function synchronize(): void
    {
        $this->checkToken();

        try {
            $status = (new RobotsManager(Uri::root() . 'sitemap.xml'))->synchronize();

            Factory::getApplication()->setUserState(
                'com_nativesitemap.robots.backup',
                $status->getBackupPath()
            );

            $messageKey = $status->getBackupPath() === null
                ? 'COM_NATIVESITEMAP_ROBOTS_ALREADY_SYNCHRONIZED'
                : 'COM_NATIVESITEMAP_ROBOTS_SYNCHRONIZED';

            $this->setRedirect(
                Route::_('index.php?option=com_nativesitemap', false),
                Text::_($messageKey),
                'success'
            );
        } catch (Throwable $exception) {
            $this->setRedirect(
                Route::_('index.php?option=com_nativesitemap', false),
                Text::sprintf(
                    'COM_NATIVESITEMAP_ROBOTS_ERROR',
                    $exception->getMessage()
                ),
                'error'
            );
        }
    }
}
