<?php

namespace CuteNinja\MemoriaBundle\Command;

use Doctrine\DBAL\Exception\ConnectionException;
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
        $entityManagers = ['default'];

        if ($additionalEntityManagers = $this->getContainer()->getParameter('additional_entity_managers')) {
            $entityManagers = array_unique(array_merge($entityManagers, $additionalEntityManagers));
            $this->generateSchemas($entityManagers);
        }

        $manager  = $this->getFixtureManager();
        $fixtures = $manager->loadFiles($this->getFixturesFiles());

        $manager->persist($fixtures);
    }

    /**
     * @param array $entityManagers
     */
    private function generateSchemas(array $entityManagers)
    {
        $defaultConnection = $this->getEntityManager()->getConnection();
        $metadata = $this->getEntityManager()->getMetadataFactory()->getAllMetadata();

        foreach ($entityManagers as $entityManager) {
            $customConnection     = $this->getEntityManager($entityManager)->getConnection();
            $connectionParameters = $customConnection->getParams();

            $databaseName = isset($connectionParameters['dbname']) ? $connectionParameters['dbname'] : null;

            if (!$databaseName) {
                throw new \InvalidArgumentException("'dbname' parameter missing.");
            }

            try {
                $shouldCreateDatabase = !in_array($databaseName, $customConnection->getSchemaManager()->listDatabases());
            } catch (ConnectionException $e) {
                $shouldCreateDatabase = true;
            }

            if ($shouldCreateDatabase) {
                $defaultConnection->getSchemaManager()->dropAndCreateDatabase($databaseName);
            }

            $tool = new SchemaTool($this->getEntityManager($entityManager));
            $tool->dropSchema($metadata);
            $tool->createSchema($metadata);
        }
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager($managerName = 'default')
    {
        return $this->getContainer()->get('doctrine')->getManager($managerName);
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
        if ($this->getContainer()->hasParameter('vendor')) {
            $vendor = $this->getContainer()->getParameter('vendor');
            foreach ($vendor['fixtures'] as $fixture) {
                $files[] = 'vendor/' . $fixture['resource'];
            }
        }

        foreach ($project['fixtures'] as $fixture) {
            $files[] = 'src/' . $baseDir . $fixture['resource'];
        }

        return $files;
    }
}
