<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping;

use ChrisAndChris\Common\RowMapperBundle\Services\Model\Model;

/**
 * @name DatabaseMapper
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class DatabaseMapper extends Model {

    /** @var array */
    private $tables;
    /** @var array */
    private $fields;
    /** @var array */
    private $relations;

    public function getFields($schema, $table) {
        if (isset($this->fields[$schema]) &&
            isset($this->fields[$schema][$table])
        ) {
            return $this->fields[$schema][$table];
        }

        // @formatter:off
        $query = $this->getDependencyProvider()->getBuilder()->select()
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
            ->close()
            ->getSqlQuery();
        // @formatter:on

        $fields = $this->runAssoc($query);
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
        $query = $this->getDependencyProvider()->getBuilder()->select()
            ->field('table_name')->alias('value')
            ->table(['information_schema', 'tables'])
            ->where()
                ->field('table_schema')->equals()->value($schema)
            ->close()
            ->getSqlQuery();
        // @formatter:on
        $this->tables[$schema] = $this->runKeyValue($query);

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
        $query = $this->getDependencyProvider()->getBuilder()->select()
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
        foreach ($this->runAssoc($query) as $relation) {
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
