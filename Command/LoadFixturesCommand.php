<?php

namespace CuteNinja\MemoriaBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use h4cc\AliceFixturesBundle\Fixtures\FixtureManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LoadFixturesCommand
 *
 * @package CuteNinja\MemoriaBundle\Command
 */
class LoadFixturesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cute_ninja:fixture:load')
            ->setDescription('Load data fixture for the current environment');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $metadata = $this->getEntityManager()->getMetadataFactory()->getAllMetadata();

        if (!empty($metadata)) {
            $tool = new SchemaTool($this->getEntityManager());
            $tool->dropSchema($metadata);
            $tool->createSchema($metadata);
        }

        $manager  = $this->getFixtureManager();
        $fixtures = $manager->loadFiles($this->getFixturesFiles());

        $manager->persist($fixtures);
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return FixtureManager
     */
    private function getFixtureManager()
    {
        return $this->getContainer()->get('h4cc_alice_fixtures.manager');
    }

    /**
     * @return array
     */
    private function getFixturesFiles()
    {
        $baseDir = $this->getContainer()->get('kernel')->getRootDir() . '/../src/CuteNinja/Bundle/';

        return array(
            $baseDir . 'RealmBundle/Resources/DataFixture/factions.yml',
            $baseDir . 'RealmBundle/Resources/DataFixture/locations.yml',

            $baseDir . 'UserBundle/Resources/DataFixture/users.yml',
            $baseDir . 'CharacterBundle/Resources/DataFixture/characterClasses.yml',
            $baseDir . 'CharacterBundle/Resources/DataFixture/characters.yml',
            $baseDir . 'CharacterBundle/Resources/DataFixture/characterHasCharacterClasses.yml',
        );
    }
}