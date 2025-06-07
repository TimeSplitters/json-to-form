<?php

/**
 * Extension for the bundle configuration
 * 
 * @author Christophe Abillama <christophe.abillama@gmail.com>
 * @license Apache-2.0
 */

namespace TimeSplitters\JsonFormBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Extension for the bundle configuration
 * 
 * @author Christophe Abillama <christophe.abillama@gmail.com>
 */
class JsonFormExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

    public function getAlias(): string
    {
        return 'timesplitters_json_to_form';
    }
}
