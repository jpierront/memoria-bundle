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
        if($this->getContainer()->hasParameter('additional_schemas')) {
            $this->createAdditionalSchemas();
        }

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

    private function createAdditionalSchemas()
    {
        $schemaManager = $this->getEntityManager()->getConnection()->getSchemaManager();
        $schemas = $this->getContainer()->getParameter('additional_schemas');

        foreach($schemas as $name)
        {
            $shouldCreateDatabase = !in_array($name, $schemaManager->listDatabases());
            if($shouldCreateDatabase) {
                $schemaManager->createDatabase($name);
            }
        }
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
        $project = $this->getContainer()->getParameter('project');
        $baseDir = array_key_exists('base_dir', $project) ? $project['base_dir'] : null;

        $files = array();
        foreach($project['fixtures'] as $fixture) {
            $files[] = 'src/' . $baseDir . $fixture['resource'];
        }

        if($this->getContainer()->hasParameter('vendor')) {
            $vendor = $this->getContainer()->getParameter('vendor');
            foreach($vendor['fixtures'] as $fixture) {
                $files[] = 'vendor/' . $fixture['resource'];
            }
        }

        return $files;
    }
}