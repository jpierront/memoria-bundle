<?php

namespace CuteNinja\MemoriaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CuteNinjaMemoriaExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if(!empty($config['additional_schemas'])) {
            $container->setParameter('additional_schemas', $config['additional_schemas']);
        }

        if(!empty($config['project'])) {
            $container->setParameter('project', $config['project']);
        }

        if(!empty($config['vendor'])) {
            $container->setParameter('vendor', $config['vendor']);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
