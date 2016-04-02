<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping;

use ChrisAndChris\Common\RowMapperBundle\Command\DatabaseMapperCommand;
use ChrisAndChris\Common\RowMapperBundle\Entity\Mapping\Field;
use ChrisAndChris\Common\RowMapperBundle\Entity\Mapping\Relation;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\MappingInitFailedException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoPrimaryKeyFoundException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoSuchColumnException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoSuchTableException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @name MappingHandler
 * @version    1.0.1
 * @since      v2.1.0
 * @lastChange v2.2.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class MappingRepository
{

    /** @var \stdClass */
    private $mapping;
    /** @var DatabaseMapperCommand */
    private $databaseMapper;

    public function __construct($cacheDir, $dir, $filename = 'mapping.json', DatabaseMapperCommand $command = null)
    {
        $this->databaseMapper = $command;
        $this->setMapping($cacheDir . '/' . $dir . '/' . basename($filename));
    }

    public function setMapping($mapping, $forceException = false)
    {
        if (is_file($mapping)) {
            $mapping = file_get_contents($mapping);
        } else {
            if ($this->databaseMapper instanceof DatabaseMapperCommand && !$forceException) {
                $this->runMapper();

                return $this->setMapping($mapping, true);
            }
            throw new MappingInitFailedException(sprintf(
                'No file found at path "%s"',
                $mapping
            ));
        }
        $this->mapping = json_decode($mapping, true);

        return true;
    }

    private function runMapper()
    {
        $this->databaseMapper->run(new ArrayInput([]), new NullOutput());
    }

    /**
     * @param string       $table   the table to check for
     * @param array|string $columns a single column or an array of columns
     * @return void
     * @throws NoSuchColumnException
     * @throws NoSuchTableException
     */
    public function hasColumns($table, $columns)
    {
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
    public function hasTable($table)
    {
        if (!isset($this->mapping[$table])) {
            throw new NoSuchTableException(sprintf(
                'No table named "%s" found',
                $table
            ));
        }
    }

    public function getRelations($table)
    {
        $this->hasTable($table);

        return $this->getRecursiveRelations($table, 1, false);
    }

    /**
     * @param     $table
     * @param int $deepness
     * @return Relation[] key is table; value is 0: source field, 1: target field
     * @throws NoSuchTableException
     */
    public function getRecursiveRelations($table, $deepness = 1, $withAliases = true)
    {
        if ($deepness === 0) {
            return [];
        }

        if (is_array($table)) {
            list ($table, $alias) = $table;
        } else {
            $alias = $table;
        }

        $relations = [];
        foreach ($this->mapping[$table]['relations'] as $relation) {
            $entity = new Relation();
            $entity->source = $alias;
            $entity->target = $relation['target'][0];
            $entity->sourceField = $relation['source'];
            $entity->targetField = $relation['target'][1];
            if ($withAliases) {
                $entity->alias = implode(null, [
                    $entity->target,
                    '_alias_',
                    $this->getTableIndex($entity->target),
                ]);
            } else {
                $entity->alias = $entity->target;
            }

            $relations[] = $entity;
            foreach ($this->getRecursiveRelations([
                $entity->target,
                $entity->alias,
            ], $deepness - 1) as $recursiveRelation) {
                $relations[] = $recursiveRelation;
            };
        }

        return $relations;
    }

    private function getTableIndex($table)
    {
        static $tableIndexes = [];

        if (!isset($tableIndexes[$table])) {
            $tableIndexes[$table] = 0;

            return $this->getTableIndex($table);
        }

        return $tableIndexes[$table]++;
    }

    /**
     * @param $table
     * @return \stdClass
     * @throws NoSuchTableException
     */
    public function getRawTable($table)
    {
        $this->hasTable($table);

        return $this->mapping[$table];
    }

    /**
     * @param $table
     * @return string
     * @throws NoPrimaryKeyFoundException
     * @throws NoSuchTableException
     */
    public function getPrimaryKeyOfTable($table)
    {
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

    private function getRawFields($table)
    {
        $this->hasTable($table);

        return $this->mapping[$table]['fields'];
    }

    /**
     * @param        $table
     * @param string $alias if not null, use this value as alias for the table name
     * @return \ChrisAndChris\Common\RowMapperBundle\Entity\Mapping\Field[]
     * @throws NoSuchTableException
     */
    public function getFields($table, $alias = null)
    {
        $this->hasTable($table);

        $fields = [];
        foreach (array_keys($this->mapping[$table]['fields']) as $field) {
            if ($alias !== null) {
                $fields[] = new Field($alias, $field);
            } else {
                $fields[] = new Field($table, $field);
            }
        }

        return $fields;
    }
}
