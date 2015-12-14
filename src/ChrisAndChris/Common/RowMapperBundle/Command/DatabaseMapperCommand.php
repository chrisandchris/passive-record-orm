<?php
namespace ChrisAndChris\Common\RowMapperBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @name DatabaseMapperCommand
 * @version
 * @since
 * @package
 * @subpackage
 * @author    Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link      http://www.klit.ch
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

        $mapper = $this->getContainer()
                       ->get('common_rowmapper.utility.mapper');
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
