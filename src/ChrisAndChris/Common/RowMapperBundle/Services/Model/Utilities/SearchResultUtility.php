<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Utilities;

use ChrisAndChris\Common\RowMapperBundle\Entity\Search\SearchContainer;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ConcreteModel;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\MappingRepository;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;

/**
 * @name SearchResult
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class SearchResultUtility
{

    /** @var MappingRepository */
    private $repository;
    /** @var SearchQueryBuilder */
    private $queryBuilder;
    /**
     * @var ConcreteModel
     */
    private $model;

    /**
     * SearchResultUtility constructor.
     *
     * @param ConcreteModel $model
     */
    public function __construct(ConcreteModel $model)
    {
        $this->model = $model;
    }

    public function setMappingRepository(MappingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function setQueryBuilder(SearchQueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Get the in statement to fetch all primary keys of an search
     *
     * @param int $searchId the unique id of the search
     * @return Builder
     */
    public function getInStatement($searchId)
    {
        // @formatter:off
        return $this->model->getDependencyProvider()->getBuilder()->in()
            ->select()
                ->field('primary_key')
            ->table('search_result')
            ->where()
                ->field('search_id')->equals()->value($searchId)
            ->close()
        ->close();
        // @formatter:on
    }

    /**
     * Get the pattern used in a search
     *
     * @param int $searchId the search id
     * @return string the pattern
     */
    public function getSearchTerm($searchId) {
        // @formatter:off
        $query = $this->model->getDependencyProvider()->getBuilder()->select()
            ->field('search_pattern')
            ->table('search')
            ->where()
                ->field('search_id')->equals()->value($searchId)
            ->close()
            ->getSqlQuery();
        // @formatter:on

        return $this->model->runWithFirstKeyFirstValue($query);
    }

    /**
     * Builds the search query and runs it
     *
     * @param SearchContainer $container
     * @return int the id of the search result
     */
    public function runSearch(SearchContainer $container)
    {
        $searchId = $this->generateSearchId($container->getTerm(), $container->targetTable);
        $query = $this->queryBuilder->buildSearchQuery($container, function () use ($searchId) {
            return $searchId;
        });
        $this->model->runSimple($query);

        $this->updateResultCount($searchId, $this->countSearchResults($searchId));

        return $searchId;
    }

    /**
     * Generates a new unique search id
     *
     * @param string $pattern     the lookup pattern
     * @param string $targetTable the table that is primary searched
     * @return int the search id
     */
    private function generateSearchId($pattern, $targetTable)
    {
        // @formatter:off
        $query = $this->model->getDependencyProvider()->getBuilder()->insert('search')
            ->fieldlist([
                'search_pattern',
                'target_table'
            ], true)
            ->values([
                [$pattern, $targetTable]
            ])
            ->getSqlQuery();
        // @formatter:off

        return $this->model->runWithLastId($query);
    }

    /**
     * Update the result count cache
     *
     * @param int $searchId    the search id to update
     * @param int $resultCount the result count to save
     */
    private function updateResultCount($searchId, $resultCount)
    {
        // @formatter:off
        $query = $this->model->getDependencyProvider()->getBuilder()->update('search')
            ->updates([
                ['result_count', $resultCount]
            ])
            ->where()
                ->field('search_id')->equals()->value($searchId)
            ->close()
            ->getSqlQuery();
        // @formatter:on

        $this->model->runSimple($query);
    }

    /**
     * Counts the search results using the search_result table
     *
     * @param int $searchId the search id
     * @return int
     */
    private function countSearchResults($searchId)
    {
        // @formatter:off
        $query = $this->model->getDependencyProvider()->getBuilder()->select()
            ->f('count')->any()->close()
            ->table('search_result')
            ->where()
                ->field('search_id')->equals()->value($searchId)
            ->close()
            ->getSqlQuery();
        // @formatter:on

        return $this->model->runWithFirstKeyFirstValue($query);
    }

    /**
     * Get the amount of rows in a search result
     *
     * @param int $searchId the id of the search
     * @return int the amount of rows in the search
     */
    public function getSearchResultCount($searchId)
    {
        // @formatter:off
        $query = $this->model->getDependencyProvider()->getBuilder()->select()
                ->field('result_count')
            ->table('search')
            ->where()
                ->field('search_id')->equals()->value($searchId)
            ->close()
            ->getSqlQuery();
        // @formatter:on

        return $this->model->runWithFirstKeyFirstValue($query);
    }
}
