<?php

namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping;

use ChrisAndChris\Common\RowMapperBundle\Entity\Mapping\Field;
use ChrisAndChris\Common\RowMapperBundle\Entity\Mapping\Relation;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\MappingException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoSuchColumnException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoSuchTableException;

/**
 * @name MappingValidator
 * @version    1.0.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class MappingValidator
{

    /** @var MappingRepository */
    private $mapper;

    /**
     * @param MappingRepository $mapper
     */
    public function __construct(MappingRepository $mapper)
    {
        $this->mapper = $mapper;
    }

    public function validateTables(array $tables)
    {
        foreach ($tables as $table) {
            try {
                $this->mapper->hasTable($table);
            } catch (NoSuchTableException $exception) {
                throw new NoSuchTableException(
                    sprintf(
                        'Did not found table "%s" in mapping',
                        $table
                    )
                );
            }
        }
    }

    /**
     * @param string  $sourceTable
     * @param Field[] $fields
     * @throws NoSuchColumnException
     */
    public function validateFields($sourceTable, array $fields)
    {
        foreach ($fields as $field) {
            try {
                if (!($field instanceof Field)) {
                    throw new MappingException(sprintf(
                        'Expected field, got %s',
                        gettype($field)
                    ));
                }
                if (empty($field->table)) {
                    $field->table = $sourceTable;
                }
                $this->validateField($field->table, $field->field);
            } catch (MappingException $exception) {
                throw new NoSuchColumnException(
                    sprintf(
                        'In table "%s", no column "%s" found',
                        (isset($field->table) ? $field->table : '(null)'),
                        (isset($field->field) ? $field->field : '(null)')
                    )
                );
            }
        }
    }

    private function validateField($table, $field)
    {
        $this->mapper->hasColumns($table, $field);
    }

    /**
     * @param            $rootTable
     * @param Relation[] $joinedTables
     * @throws NoSuchTableException
     */
    public function validateJoins($rootTable, array $joinedTables)
    {
        $relations = $this->mapper->getRelations($rootTable);
        $count = 0;
        foreach ($joinedTables as $join) {
            foreach ($relations as $relation) {
                if ($relation->target == $join->target) {
                    $count++;
                    break;
                }
            }
        }

        if (count($joinedTables) !== $count) {
            throw new NoSuchTableException(sprintf(
                'There are invalid joins (at least %d)',
                count($joinedTables) - $count
            ));
        }
    }
}
