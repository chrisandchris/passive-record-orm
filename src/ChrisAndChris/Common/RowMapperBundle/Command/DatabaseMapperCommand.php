<?php
namespace ChrisAndChris\Common\RowMapperBundle\Command;

use ChrisAndChris\Common\RowMapperBundle\Events\RowMapperEvents;
use ChrisAndChris\Common\RowMapperBundle\Events\Transmitters\MapperEvent;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @name DatabaseMapperCommand
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class DatabaseMapperCommand extends ContainerAwareCommand {

    /** @var InputInterface */
    private $input;
    /** @var OutputInterface */
    private $output;

    /**
     * @inheritDoc
     */
    protected function configure() {
        $this->setName('cac:database:mapper');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->input = $input;
        $this->output = $output;

        // @todo extract mapper as event subscriber, collect all of them
        // @todo select correct subsystem, run mapper with subsystem

        $event = $this->getContainer()
                      ->get('event_dispatcher')
                      ->dispatch(RowMapperEvents::MAPPER_COLLECTOR, new MapperEvent());

        $subsystem = $this->getSubsystem(
            $this->getContainer()
                 ->getParameter('database_driver')
        );

        $mapper = $event->getMapper($subsystem);
        
        $schema = $this->getContainer()
                       ->getParameter('database_name');

        $tables = $mapper->getTables($schema);
        $fields = [];
        $relations = [];
        foreach ($tables as $table) {
            $fields[$table] = $mapper->getFields($schema, $table);
            $relations[$table] = $mapper->getRelations($schema, $table);
        }

        $result = $this->merge($tables, $fields, $relations);
        $this->writeCache($result);
    }

    private function getSubsystem($subsystem)
    {
        $tests = [
            'mysql',
            'pgsql',
            'sqlite',
        ];
        foreach ($tests as $test) {
            if (strstr($subsystem, $test) !== false) {
                return $test;
            }
        }

        return false;
    }

    private function merge(array $tables, array $fields, array $relations) {
        $result = [];
        foreach ($tables as $table) {
            $result[$table] = [
                'fields'    => $fields[$table],
                'relations' => $relations[$table],
            ];
        }

        return $result;
    }

    private function writeCache(array $result) {
        $writer = $this->getContainer()
                       ->get('common_rowmapper.utilty.cache_writer');

        $writer->writeToCache('mapping.json', json_encode($result));
    }
}
