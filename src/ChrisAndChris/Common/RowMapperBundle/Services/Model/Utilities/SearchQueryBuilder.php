<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Utilities;

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
        $this->repository->hasTable($searchContainer->rootTable);

        if ($searchContainer->targetTable === null) {
            $searchContainer->targetTable =
                'search_' . $searchContainer->rootTable;
        }
        $this->repository->hasTable($searchContainer->targetTable);

        if ($searchContainer->joinedTables === null) {
            $searchContainer->joinedTables =
                $this->repository->getRecursiveRelations($searchContainer->rootTable);
        } else {
            $this->validator->validateJoins($searchContainer->rootTable, $searchContainer->joinedTables);
        }

        if ($searchContainer->searchId !== null) {
            $searchContainer->joinedTables[$searchContainer->targetTable] =
                'search_id';
        }

        if ($searchContainer->lookupFields === null) {
            $searchContainer->lookupFields =
                $this->repository->getFields($searchContainer->rootTable);
            foreach (array_keys($searchContainer->joinedTables) as $join) {
                $this->repository->hasTable($join);
                foreach ($this->repository->getFields($join) as $field) {
                    $searchContainer->lookupFields[] = $join . ':' . $field;
                }
            }
        } else {
            $this->validator->validateFields($searchContainer->rootTable, $searchContainer->lookupFields);
        }

        $searchContainer->primaryKey =
            $this->repository->getPrimaryKeyOfTable($searchContainer->rootTable);

        return $searchContainer;
    }

    /**
     * @param SearchContainer $searchContainer
     * @param \Closure        $searchId
     * @return SqlQuery
     * @throws MissingParameterException
     */
    public function buildSearchQuery(SearchContainer $searchContainer, \Closure $searchId) {

        $searchQuery = $this->buildBaseSearchQuery($searchContainer, $searchId);

        $this->buildJoinedTables($searchQuery, $searchContainer->rootTable, $searchContainer->joinedTables);
        $this->buildLookupFields($searchQuery, $searchContainer->lookupFields, $searchContainer->term);
        $this->buildFilterConditions($searchQuery, $searchContainer);

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
        return $this->getBuilder()->insert($container->targetTable, 'ignore')
            ->brace()
                ->field('search_id')->c()
                ->field($container->primaryKey)
            ->close()
            ->select()
                ->value($searchId)->c()
                ->field($container->primaryKey)
            ->table($container->rootTable);
        // @formatter:on
    }

    /**
     * @return Builder
     */
    private function getBuilder() {
        return $this->builderFactory->createBuilder();
    }

    /**
     * @param       $lookupTable
     * @param array $joinedTables
     * @param       $searchQuery
     */
    private function buildJoinedTables(Builder $searchQuery, $lookupTable, array $joinedTables) {
        /*
                 * How to format $joinedTables:
                 *
                 * 1. Simple usage
                 *      Key is table, value is using-field
                 * 2. Extended usage
                 *      Key is table, value is an array with
                 *          Values left-table-field, right-table-field
                 *      Generates an on() instead of using()
                 * 3. Alias usage
                 *      Key is table formatted "alias:table-name"
                 */
        foreach ($joinedTables as $table => $using) {
            $alias = $table;
            if (strstr($table, ':')) {
                list($alias, $table) = explode(':', $table, 2);
            }

            if ($alias !== null) {
                $searchQuery->join($table, 'left')
                            ->alias($alias);
            } else {
                $searchQuery->join($table, 'left');
            }

            if (is_array($using)) {
                $searchQuery->on()
                            ->field([$lookupTable, $using[0]])
                            ->equals();
                if ($alias === null) {
                    $searchQuery->field($using);
                } else {
                    $searchQuery->field([$alias, $using[1]]);
                }
                $searchQuery->close();
            } else {
                $searchQuery->using($using);
            }
        }
    }

    /**
     * @param array $lookupFields
     * @param       $term
     * @param       $searchQuery
     */
    private function buildLookupFields(Builder $searchQuery, array $lookupFields, $term) {
        $searchQuery->where()
                    ->brace();

        foreach ($lookupFields as $idx => $field) {
            $searchQuery->field($field)
                        ->like($term . '%');
            if ($idx < count($lookupFields) - 1) {
                $searchQuery->connect('or');
            }
        }
    }

    /**
     * @param Builder         $searchQuery
     * @param SearchContainer $searchContainer
     * @throws \Exception
     * @internal param array $filterConditions
     */
    private function buildFilterConditions(Builder $searchQuery, SearchContainer $searchContainer) {
        /*
         * How to format $filterConditions
         *
         * 1. Simple usage
         *      Key is field, value is requested value
         * 2. Extended usage
         *      Key is field, Value is an array with
         *          Values connector (and, or, ...) to previous statement
         *          and then the requested value
         */
        if (count($searchContainer->filterConditions) > 0 ||
            $searchContainer->searchId !== null
        ) {
            $searchQuery->close()
                        ->connect('&')
                        ->brace();
            if (count($searchContainer->filterConditions) > 0 &&
                $searchContainer->searchId !== null
            ) {
                $searchQuery->brace();
            }

            $count = 0;
            $length = count($searchContainer->filterConditions);
            foreach ($searchContainer->filterConditions as $field =>
                     $requestedValue) {
                $connector = '&';
                if (is_array($requestedValue)) {
                    $connector = $requestedValue[0];
                    $requestedValue = $requestedValue[1];
                }
                if ($count++ != 0 && $count <= $length) {
                    $searchQuery->connect($connector);
                }
                $searchQuery->field($field)
                            ->equals()
                            ->value($requestedValue);
            }

            if ($searchContainer->searchId !== null) {
                if (count($searchContainer->filterConditions) > 0) {
                    $searchQuery->close()
                                ->connect('&');
                }
                $searchQuery->field('search_id')
                            ->equals()
                            ->value($searchContainer->searchId);
            }
        }
        $searchQuery->close();
    }
}
