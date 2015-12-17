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

    /**
     * @param            $root
     * @param Relation[] $joins
     * @throws MappingException
     * @throws NoSuchTableException
     */
    public function validateJoins($root, array $joins) {

        $availableJoins = $this->mapper->getRecursiveRelations($root);

        $count = 0;

        foreach ($availableJoins as $join) {
            foreach ($joins as $givenJoin) {
                if (!($givenJoin instanceof Relation)) {
                    throw new MappingException(sprintf(
                        'Expected Relation object, got %s',
                        gettype($givenJoin)
                    ));
                }
                if ($givenJoin->source == $join->source && $givenJoin->target == $join->target &&
                    $givenJoin->sourceField == $join->sourceField && $givenJoin->targetField && $join->targetField
                ) {
                    $count++;
                    break;
                }
            }
        }

        if ($count !== count($joins)) {
            throw new NoSuchTableException(
                sprintf('Not every join table available for "%s"',
                    $root
                )
            );
        }
    }

    /**
     * @param string  $sourceTable
     * @param Field[] $fields
     * @throws NoSuchColumnException
     */
    public function validateFields($sourceTable, array $fields) {
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
                        $field->table,
                        $field->field
                    )
                );
            }
        }
    }

    private function validateField($table, $field) {
        $this->mapper->hasColumns($table, $field);
    }
}
