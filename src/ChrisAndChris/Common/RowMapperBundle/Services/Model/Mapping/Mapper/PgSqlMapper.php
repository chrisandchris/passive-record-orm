<?php

namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\Mapper;

use ChrisAndChris\Common\RowMapperBundle\Events\RowMapperEvents;
use ChrisAndChris\Common\RowMapperBundle\Events\Transmitters\MapperEvent;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ConcreteModel;

/**
 * @name PgSqlMapper
 * @version    1.0.0
 * @since      v2.2.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class PgSqlMapper implements MapperInterface
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
        $event->add($this, ['pgsql']);
    }

    public function getFields($schema, $table)
    {
        $schema = 'public';
        if (isset($this->fields[$schema]) &&
            isset($this->fields[$schema][$table])
        ) {
            return $this->fields[$schema][$table];
        }

        // @formatter:off
        $query = $this->model->getDependencyProvider()->getBuilder()->select()
            ->fieldlist([
                'table_name',
                'column_name',
                'column_default',
                'data_type',
                'constraint_type',
            ])
            ->table(['information_schema', 'columns'])
            ->join(['information_schema', 'table_constraints'], 'left')
                ->using(['table_catalog', 'table_schema', 'table_name'])
            ->where()
                ->field('table_schema')->equals()->value($schema)
            ->close()
            ->getSqlQuery();
        // @formatter:on

        $fields = $this->model->runAssoc($query);
        foreach ($fields as $field) {
            if (!isset($this->fields[$schema][$field['table_name']])) {
                $this->fields[$schema][$field['table_name']] = [];
            }
            $this->fields[$schema][$field['table_name']][$field['column_name']] =
                [
                    'type'  => $field['data_type'],
                    'key'   => $this->parseKey($field['constraint_type']),
                    'extra' => $this->parseExtra($field['column_default']),
                ];
        }

        return $this->getFields($schema, $table);
    }

    private function parseKey($columnKey)
    {
        switch ($columnKey) {
            case 'PRIMARY KEY' :
                return 'primary';
            default:
                return null;
        }
    }

    private function parseExtra($extra)
    {
        if (strstr($extra, 'nextval(')) {
            return 'increment';
        }

        return null;
    }

    public function getTables($schema)
    {
        $schema = 'public';
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

    public function getRelations($schema, $table)
    {
        $schema = 'public';
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
                'tc:table_name' => 'table_name',
                'kcu:column_name' => 'column_name',
                'ccu:table_name' => 'referenced_table_name',
                'ccu:column_name' => 'referenced_column_name'
            ])
            ->table(['information_schema', 'table_constraints'], 'tc')
            ->join(['information_schema', 'key_column_usage'], 'inner', 'kcu')
                ->using('constraint_name')
            ->join(['information_schema', 'constraint_column_usage'], 'inner', 'ccu')
                ->using('constraint_name')
            ->where()
                ->field(['tc', 'table_schema'])->equals()->value($schema)
            ->close()
            ->getSqlQuery();
        // @formatter:on
        foreach ($this->model->runAssoc($query) as $relation) {
            $this->relations[$schema][$relation['table_name']][] = [
                'source' => $relation['column_name'],
                'target' => [
                    $relation['referenced_table_name'],
                    $relation['referenced_column_name'],
                ],
            ];
        }

        return $this->getRelations($schema, $table);
    }
}
