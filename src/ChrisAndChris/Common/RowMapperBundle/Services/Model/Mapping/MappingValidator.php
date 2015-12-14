<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping;

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
class MappingValidator {

    /** @var MappingRepository */
    private $mapper;

    /**
     * @param MappingRepository $mapper
     */
    public function __construct(MappingRepository $mapper) {
        $this->mapper = $mapper;
    }

    public function validateTables(array $tables) {
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

    public function validateJoins($root, array $joins) {

        $availableJoins = $this->mapper->getCircularRelations($root);

        $count = 0;
        foreach ($joins as $join) {
            if (isset($availableJoins[$join])) {
                $count++;
            }
        }

        if ($count !== count($joins)) {
            throw new NoSuchTableException(
                sprintf(
                    'Not every join table available for "%s"',
                    $root
                )
            );
        }
    }

    public function validateFields($sourceTable, array $fields) {
        foreach ($fields as $field) {
            $table = $sourceTable;
            try {
                if (strstr($field, ':') !== false) {
                    list($table, $field) = explode(':', $field);
                }
                $this->validateField($table, $field);
            } catch (MappingException $exception) {
                throw new NoSuchColumnException(
                    sprintf(
                        'In table "%s", no column "%s" found',
                        $table,
                        $field
                    )
                );
            }
        }
    }

    private function validateField($table, $field) {
        $this->mapper->hasColumns($table, $field);
    }
}
