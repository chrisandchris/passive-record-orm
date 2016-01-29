<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Utilities;

use ChrisAndChris\Common\RowMapperBundle\Entity\Mapping\Field;
use ChrisAndChris\Common\RowMapperBundle\Entity\Mapping\Relation;
use ChrisAndChris\Common\RowMapperBundle\Entity\Search\SearchContainer;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoPrimaryKeyFoundException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoSuchColumnException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoSuchTableException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\MissingParameterException;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\MappingRepository;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\MappingValidator;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\BuilderFactory;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\SqlQuery;

/**
 * @name SearchableModel
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class SearchQueryBuilder {

    /** @var MappingValidator */
    private $validator;
    /** @var MappingRepository */
    private $repository;
    /** @var BuilderFactory */
    private $builderFactory;
    /** @var array */
    private $tableIndexes = [];

    /**
     * @param BuilderFactory    $builderFactory
     * @param MappingRepository $repository
     * @param MappingValidator  $validator
     */
    public function __construct(BuilderFactory $builderFactory, MappingRepository $repository, MappingValidator $validator) {
        $this->builderFactory = $builderFactory;
        $this->repository = $repository;
        $this->validator = $validator;
    }

    /**
     * Search by a specific pattern
     *
     * @param SearchContainer $searchContainer
     * @return SearchContainer
     * @throws MissingParameterException
     * @throws NoSuchColumnException
     * @throws NoSuchTableException
     * @throws NoPrimaryKeyFoundException
     */
    public function buildSearchContainer(SearchContainer $searchContainer) {
        $this->tableIndexes = [];
        $this->repository->hasTable($searchContainer->getRootTable());

        // add primary key
        $searchContainer->primaryKey = $this->repository->getPrimaryKeyOfTable($searchContainer->getRootTable());

        // validate target table
        if ($searchContainer->targetTable === null) {
            $searchContainer->targetTable = $searchContainer->getRootTable();
        }
        $this->repository->hasTable($searchContainer->targetTable);

        // validate joins
        if (count($searchContainer->getJoinedTables()) == 0) {
            $relations = $this->repository->getRecursiveRelations($searchContainer->getRootTable());
            foreach ($relations as $join) {
                $searchContainer->addJoin($join);
            }
        } else {
            $this->validator->validateJoins($searchContainer->getRootTable(), $searchContainer->getJoinedTables());
        }

        // validate previous search id
        if ($searchContainer->getSearchId() !== null) {
            $searchContainer->addJoin(new Relation(
                $searchContainer->getRootTable(),
                'search_result',
                $searchContainer->primaryKey,
                'primary_key'
            ));
        }

        // validate fields
        if ($searchContainer->getLookupFields() === null) {
            // add root table fields
            $fields = $this->repository->getFields($searchContainer->getRootTable());
            foreach ($fields as $field) {
                $searchContainer->addLookup($field);
            }

            // add fields of joined tables
            foreach ($searchContainer->getJoinedTables() as $join) {
                $this->repository->hasTable($join->target);
                foreach ($this->repository->getFields($join->target, $join->alias) as $field) {
                    $searchContainer->addLookup($field);
                }
            }
        } else {
            $this->validator->validateFields($searchContainer->getRootTable(), $searchContainer->getLookupFields());
            foreach ($searchContainer->getLookupFields() as $lookupField) {
                foreach ($searchContainer->getJoinedTables() as $joinedTable) {
                    if ($lookupField->table == $joinedTable->target &&
                        $lookupField->table !== $searchContainer->getRootTable()
                    ) {
                        $lookupField->table = $joinedTable->alias;
                    }
                }
            }
        }

        return $searchContainer;
    }

    /**
     * @param SearchContainer $searchContainer
     * @param \Closure        $searchId
     * @return SqlQuery
     */
    public function buildSearchQuery(SearchContainer $searchContainer, \Closure $searchId) {

        $searchQuery = $this->buildBaseSearchQuery($searchContainer, $searchId);

        $this->buildJoinedTables($searchQuery, $searchContainer->getJoinedTables());

        $searchQuery->where()
                    ->brace();

        $this->buildLookupFields($searchQuery, $searchContainer->getLookupFields(), $searchContainer->getTerm());

        $searchQuery->close();

        if (count($searchContainer->getFilterConditions()) > 0 || $searchContainer->getSearchId() !== null) {
            $searchQuery->connect('&')
                        ->brace();
            $this->buildFilterConditions($searchQuery, $searchContainer, $searchContainer->getSearchId() !== null);
            $searchQuery->close();
        }

        return $searchQuery->close()
                           ->getSqlQuery();
    }

    /**
     * @param SearchContainer $container
     * @param \Closure        $searchId
     * @return Builder
     */
    private function buildBaseSearchQuery(SearchContainer $container, \Closure $searchId) {
        // @formatter:off
        return $this->getBuilder()->insert('search_result', 'ignore')
            ->brace()
                ->field('search_id')->c()
                ->field('primary_key')
            ->close()
            ->select()
                ->value($searchId)->c()
                ->field([$container->getRootTable(), $container->primaryKey])
            ->table($container->getRootTable());
        // @formatter:on
    }

    /**
     * @return Builder
     */
    private function getBuilder() {
        return $this->builderFactory->createBuilder();
    }

    /**
     * @param Builder    $searchQuery
     * @param Relation[] $joinedTables
     */
    private function buildJoinedTables(Builder $searchQuery, array $joinedTables)
    {
        /** @var Relation $relation */
        foreach ($joinedTables as $relation) {
            // @formatter:off
            $searchQuery->join($relation->target, 'left')->alias($relation->alias)
                        ->on()
                            ->field([$relation->source, $relation->sourceField])
                            ->equals()
                            ->field([$relation->alias, $relation->targetField])
                        ->close();
            // @formatter:on
        }
    }

    /**
     * @param Builder $searchQuery
     * @param Field[] $lookupFields
     * @param string  $term
     */
    private function buildLookupFields(Builder $searchQuery, array $lookupFields, $term) {
        $count = count($lookupFields);
        foreach ($lookupFields as $idx => $field) {
            if ($term === null) {
                $searchQuery->field([$field->table, $field->field])
                            ->isNull();
            } else {
                $searchQuery->field([$field->table, $field->field])
                            ->like($term);
            }
            if ($idx < $count - 1) {
                $searchQuery->connect('or');
            }
        }
    }

    /**
     * @param Builder         $searchQuery
     * @param SearchContainer $searchContainer
     * @param bool            $hasSearchId
     */
    private function buildFilterConditions(Builder $searchQuery, SearchContainer $searchContainer, $hasSearchId)
    {
        if ($hasSearchId) {
            $searchQuery->brace();
        }

        $count = 0;
        $length = count($searchContainer->getFilterConditions());

        foreach ($searchContainer->getFilterConditions() as $condition) {
            if ($count++ != 0 && $count <= $length) {
                $searchQuery->connect('&');
            }
            $searchQuery->field([$condition->field, $condition->table])
                        ->equals()
                        ->value($condition->requestValue);
        }

        if ($hasSearchId) {
            if (count($searchContainer->getFilterConditions()) > 0) {
                $searchQuery->close()
                            ->connect('&');
            }
            $searchQuery->field(['search_result', 'search_id'])
                        ->equals()
                        ->value($searchContainer->getSearchId());
        }
    }
}
