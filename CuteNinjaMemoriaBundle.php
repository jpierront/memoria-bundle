<?php

namespace CuteNinja\MemoriaBundle;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CuteNinjaMemoriaBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

    public function boot()
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $platform      = $entityManager->getConnection()->getDatabasePlatform();

        $platform->registerDoctrineTypeMapping('enum', 'string');
    }
}
