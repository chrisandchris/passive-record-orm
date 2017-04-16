<?php

namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\Mapper;

use ChrisAndChris\Common\RowMapperBundle\Events\RowMapperEvents;
use ChrisAndChris\Common\RowMapperBundle\Events\Transmitters\MapperEvent;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ConcreteModel;

/**
 * A mapper for mysql databases
 *
 * @name MySqlMapper
 * @version    1.0.0
 * @since      v2.2.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class MySqlMapper implements MapperInterface
{

    /** @var array */
    private $tables;
    /** @var array */
    private $fields;
    /** @var array */
    private $relations;
    /** @var ConcreteModel */
    private $model;

    /**
     * @param ConcreteModel $model
     */
    public function __construct(ConcreteModel $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            RowMapperEvents::MAPPER_COLLECTOR => ['onCollectorEvent'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function onCollectorEvent(MapperEvent $event)
    {
        $event->add($this, ['mysql']);
    }

    public function getFields($schema, $table) {
        if (isset($this->fields[$schema]) &&
            isset($this->fields[$schema][$table])
        ) {
            return $this->fields[$schema][$table];
        }

        // @formatter:off
        $query = $this->model->getDependencyProvider()->getBuilder()->select()
            ->fieldlist([
                'TABLE_NAME',
                'COLUMN_NAME',
                'COLUMN_DEFAULT',
                'DATA_TYPE',
                'COLUMN_KEY',
                'EXTRA'
            ])
            ->table(['information_schema', 'columns'])
            ->where()
                ->field('TABLE_SCHEMA')->equals()->value($schema)
                ->connect()
                ->field('TABLE_NAME')->equals()->value($table)
            ->close()
            ->getSqlQuery();
        // @formatter:on

        $fields = $this->model->runAssoc($query);
        if (count($fields) ==0) {
            $this->fields[$schema][$table] = [];
        }
        foreach ($fields as $field) {
            if (!isset($this->fields[$schema][$field['TABLE_NAME']])) {
                $this->fields[$schema][$field['TABLE_NAME']] = [];
            }
            $this->fields[$schema][$field['TABLE_NAME']][$field['COLUMN_NAME']] =
                [
                    'type'  => $field['DATA_TYPE'],
                    'key'   => $this->parseKey($field['COLUMN_KEY']),
                    'extra' => $this->parseExtra($field['EXTRA']),
                ];
        }

        return $this->getFields($schema, $table);
    }

    private function parseKey($columnKey) {
        switch ($columnKey) {
            case 'PRI' :
                return 'primary';
            default:
                return null;
        }
    }

    private function parseExtra($extra) {
        switch ($extra) {
            case 'auto_increment' :
                return 'increment';
            default:
                return null;
        }
    }

    public function getTables($schema) {
        if (isset($this->tables[$schema])) {
            return $this->tables[$schema];
        }

        // @formatter:off
        $query = $this->model->getDependencyProvider()->getBuilder()->select()
            ->field('table_name')->alias('value')
            ->table(['information_schema', 'tables'])
            ->where()
                ->field('table_schema')->equals()->value($schema)
            ->close()
            ->getSqlQuery();
        // @formatter:on
        $this->tables[$schema] = $this->model->runKeyValue($query);

        return $this->getTables($schema);
    }

    public function getRelations($schema, $table) {
        if (!empty($this->relations)) {
            if (isset($this->relations[$schema]) &&
                isset($this->relations[$schema][$table])
            ) {
                return $this->relations[$schema][$table];
            }

            return [];
        }

        // @formatter:off
        $query = $this->model->getDependencyProvider()->getBuilder()->select()
            ->fieldlist([
                'TABLE_NAME',
                'COLUMN_NAME',
                'REFERENCED_TABLE_NAME',
                'REFERENCED_COLUMN_NAME'
            ])
            ->table(['information_schema', 'key_column_usage'])
            ->where()
                ->field('REFERENCED_TABLE_SCHEMA')->equals()->value($schema)
                ->connect()
                ->field('TABLE_SCHEMA')->equals()->value($schema)
            ->close()
            ->getSqlQuery();
        // @formatter:on
        foreach ($this->model->runAssoc($query) as $relation) {
            $this->relations[$schema][$relation['TABLE_NAME']][] = [
                'source' => $relation['COLUMN_NAME'],
                'target' => [
                    $relation['REFERENCED_TABLE_NAME'],
                    $relation['REFERENCED_COLUMN_NAME'],
                ],
            ];
        }

        return $this->getRelations($schema, $table);
    }
}
