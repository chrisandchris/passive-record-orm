<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoPrimaryKeyFoundException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoSuchColumnException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoSuchTableException;

/**
 * @name MappingHandler
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class MappingRepository {

    /** @var \stdClass */
    private $mapping;

    public function __construct($cacheDir, $dir) {
        $this->setMapping($cacheDir . '/' . $dir . '/mapping.json');
    }

    public function setMapping($mapping) {
        if (is_file($mapping)) {
            $mapping = file_get_contents($mapping);
        }
        $this->mapping = json_decode($mapping, true);
    }

    /**
     * @param string       $table   the table to check for
     * @param array|string $columns a single column or an array of columns
     * @return void
     * @throws NoSuchColumnException
     * @throws NoSuchTableException
     */
    public function hasColumns($table, $columns) {
        $this->hasTable($table);

        if (!is_array($columns)) {
            $columns = [$columns];
        }

        foreach ($columns as $column) {
            if (!isset($this->mapping[$table]['fields'][$column])) {
                throw new NoSuchColumnException(
                    sprintf(
                        'No column "%s" for table "%s" found',
                        $table,
                        $column
                    )
                );
            }
        }
    }

    /**
     * @param $table
     * @return void
     * @throws NoSuchTableException
     */
    public function hasTable($table) {
        if (!isset($this->mapping[$table])) {
            throw new NoSuchTableException(
                sprintf(
                    'No table named "%s" found',
                    $table
                )
            );
        }
    }

    /**
     * @param     $table
     * @param int $deepness
     * @return array key is table; value is 0: source field, 1: target field
     * @throws NoSuchTableException
     */
    public function getRecursiveRelations($table, $deepness = 3) {
        $this->hasTable($table);

        if ($deepness === 0) {
            return [];
        }

        $relations = [];
        foreach ($this->mapping[$table]['relations'] as $relation) {
            $relations[$relation['target'][0]] = [
                $relation['source'],
                $relation['target'][1],
            ];
            $circularRelation = $this->getRecursiveRelations(
                $relation['target'][0], $deepness - 1
            );
            foreach ($circularRelation as $targetTable => $fields) {
                $relations[$targetTable] = $fields;
            }
        }

        return $relations;
    }

    /**
     * @param $table
     * @return \stdClass
     * @throws NoSuchTableException
     */
    public function getTable($table) {
        $this->hasTable($table);

        return $this->mapping[$table];
    }

    /**
     * @param $table
     * @return string
     * @throws NoPrimaryKeyFoundException
     * @throws NoSuchTableException
     */
    public function getPrimaryKeyOfTable($table) {
        $this->hasTable($table);

        foreach ($this->getRawFields($table) as $field => $option) {
            if ($option['key'] == 'primary') {
                return $field;
            }
        }

        throw new NoPrimaryKeyFoundException(
            sprintf(
                'Unable to find primary key for table "%s"',
                $table
            )
        );
    }

    private function getRawFields($table) {
        $this->hasTable($table);

        return $this->mapping[$table]['fields'];
    }

    /**
     * @param $table
     * @return array
     * @throws NoSuchTableException
     */
    public function getFields($table) {
        $this->hasTable($table);

        $fields = [];
        foreach (array_keys($this->mapping[$table]['fields']) as $field) {
            $fields[] = $field;
        }

        return $fields;
    }
}
