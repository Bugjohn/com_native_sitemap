<?php

declare(strict_types=1);

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nativesitemap
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory as ComponentDispatcherFactoryServiceProvider;
use Joomla\CMS\Extension\Service\Provider\MVCFactory as MVCFactoryServiceProvider;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use OrkaCS\Component\Nativesitemap\Administrator\Extension\NativeSitemapComponent;

return new class implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->registerServiceProvider(
            new MVCFactoryServiceProvider('\\OrkaCS\\Component\\Nativesitemap')
        );

        $container->registerServiceProvider(
            new ComponentDispatcherFactoryServiceProvider('\\OrkaCS\\Component\\Nativesitemap')
        );

        $container->set(
            ComponentInterface::class,
            static function (Container $container): NativeSitemapComponent {
                $component = new NativeSitemapComponent(
                    $container->get(ComponentDispatcherFactoryInterface::class)
                );

                $component->setMVCFactory(
                    $container->get(MVCFactoryInterface::class)
                );

                return $component;
            }
        );
    }
};
