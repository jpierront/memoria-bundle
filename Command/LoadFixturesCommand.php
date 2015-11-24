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

        if ($this->getContainer()->hasParameter('additional_entity_managers')) {
            $this->deleteAdditionalSchemas();
            $this->createAdditionalSchemas();
            $entityManagers = array_unique(array_merge($entityManagers, $this->getContainer()->getParameter('additional_entity_managers')));
        }

        $metadata = $this->getEntityManager()->getMetadataFactory()->getAllMetadata();

        if (!empty($metadata)) {
            foreach ($entityManagers as $entityManagerName) {
                $tool = new SchemaTool($this->getEntityManager($entityManagerName));
                $tool->dropSchema($metadata);
                $tool->createSchema($metadata);
            }
        }

        $manager  = $this->getFixtureManager();
        $fixtures = $manager->loadFiles($this->getFixturesFiles());

        $manager->persist($fixtures);
    }

    /**
     * Create the defined additional schemas
     */
    private function createAdditionalSchemas()
    {
        $additionalEntityManagers = $this->getContainer()->getParameter('additional_entity_managers');
        $defaultConnection         = $this->getEntityManager()->getConnection();

        foreach ($additionalEntityManagers as $additionalEntityManagerName) {

            $customConnection     = $this->getEntityManager($additionalEntityManagerName)->getConnection();
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
                $defaultConnection->getSchemaManager()->createDatabase($databaseName);
            }
        }
    }

    /**
     * Delete the defined additional schemas
     */
    private function deleteAdditionalSchemas()
    {
        $additionalEntityManagers = $this->getContainer()->getParameter('additional_entity_managers');
        $defaultConnection         = $this->getEntityManager()->getConnection();

        foreach ($additionalEntityManagers as $additionalEntityManagerName) {

            $customConnection     = $this->getEntityManager($additionalEntityManagerName)->getConnection();
            $connectionParameters = $customConnection->getParams();

            $databaseName = isset($connectionParameters['dbname']) ? $connectionParameters['dbname'] : null;

            if (!$databaseName) {
                throw new \InvalidArgumentException("'dbname' parameter missing.");
            }

            try {
                $shouldDropDatabase = in_array($databaseName, $customConnection->getSchemaManager()->listDatabases());
            } catch (ConnectionException $e) {
                $shouldDropDatabase = false;
            }

            if ($shouldDropDatabase) {
                $defaultConnection->getSchemaManager()->dropDatabase($databaseName);
            }
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
                $files[] = 'vendor/'.$fixture['resource'];
            }
        }

        foreach ($project['fixtures'] as $fixture) {
            $files[] = 'src/'.$baseDir.$fixture['resource'];
        }


        return $files;
    }
}