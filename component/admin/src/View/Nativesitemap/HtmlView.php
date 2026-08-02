<?php

declare(strict_types=1);

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nativesitemap
 */

namespace OrkaCS\Component\Nativesitemap\Administrator\View\Nativesitemap;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use OrkaCS\Component\Nativesitemap\Administrator\Model\RobotsStatus;
use OrkaCS\Component\Nativesitemap\Administrator\Service\RobotsManager;
use Throwable;

final class HtmlView extends BaseHtmlView
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $diagnostic = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $sitemap = null;

    public ?RobotsStatus $robotsStatus = null;

    public ?string $robotsError = null;

    public ?string $robotsBackup = null;

    public function display($tpl = null): void
    {
        $application = Factory::getApplication();

        $this->diagnostic = $application->getUserState(
            'com_nativesitemap.diagnostic'
        );

        $this->sitemap = $application->getUserState(
            'com_nativesitemap.sitemap'
        );

        $this->robotsBackup = $application->getUserState(
            'com_nativesitemap.robots.backup'
        );

        try {
            $this->robotsStatus = (new RobotsManager(
                Uri::root() . 'sitemap.xml'
            ))->analyse();
        } catch (Throwable $exception) {
            $this->robotsError = $exception->getMessage();
        }

        ToolbarHelper::title(Text::_('COM_NATIVESITEMAP_TITLE'), 'sitemap');
        ToolbarHelper::custom(
            'sitemap.generate',
            'download',
            '',
            'COM_NATIVESITEMAP_GENERATE_SITEMAP',
            false
        );
        ToolbarHelper::custom(
            'robots.synchronize',
            'refresh',
            '',
            'COM_NATIVESITEMAP_SYNCHRONIZE_ROBOTS',
            false
        );
        ToolbarHelper::custom(
            'diagnostic.run',
            'refresh',
            '',
            'COM_NATIVESITEMAP_TEST_COLLECTION',
            false
        );

        parent::display($tpl);
    }
}
