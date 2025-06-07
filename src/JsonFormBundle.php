<?php

/**
 * Bundle Symfony pour transformer des structures JSON en formulaires dynamiques
 * 
 * @author Christophe Abillama <christophe.abillama@gmail.com>
 * @license Apache-2.0
 */

namespace TimeSplitters\JsonFormBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use TimeSplitters\JsonFormBundle\DependencyInjection\JsonFormExtension;

/**
 * Bundle principal pour la transformation JSON vers formulaires Symfony
 */
class JsonFormBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new JsonFormExtension();
    }
}
