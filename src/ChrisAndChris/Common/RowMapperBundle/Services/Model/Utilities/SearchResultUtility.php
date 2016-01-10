<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Utilities;

use ChrisAndChris\Common\RowMapperBundle\Entity\Search\SearchContainer;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\MappingRepository;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Model;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;

/**
 * @name SearchResult
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class SearchResultUtility extends Model {

    /** @var MappingRepository */
    private $repository;
    /** @var SearchQueryBuilder */
    private $queryBuilder;

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
     * @param string $table    the table the search was performed on
     * @param int    $searchId the unique id of the search
     * @return Builder
     */
    public function getInStatement($table, $searchId) {
        // @formatter:off
        return $this->getDependencyProvider()->getBuilder()->in()
            ->select()
                ->field($this->repository->getPrimaryKeyOfTable($table))
            ->table('search_' . $table)
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
        $query = $this->getDependencyProvider()->getBuilder()->select()
            ->field('search_pattern')
            ->table('search')
            ->where()
                ->field('search_id')->equals()->value($searchId)
            ->close()
            ->getSqlQuery();
        // @formatter:on

        return $this->runWithFirstKeyFirstValue($query);
    }

    /**
     * Builds the search query and runs it
     *
     * @param SearchContainer $container
     * @return int the id of the search result
     */
    public function runSearch(SearchContainer $container)
    {
        $this->_startTransaction();

        $searchId = $this->generateSearchId($container->getTerm(), $container->targetTable);
        $query = $this->queryBuilder->buildSearchQuery($container, function () use ($searchId) {
            return $searchId;
        });
        $this->runSimple($query);

        $resultCount = $this->getSearchResultCount($searchId);
        $this->updateResultCount($searchId, $resultCount);

        $this->_commit();

        return $searchId;
    }

    /**
     * Generates a new unique search id
     *
     * @param $pattern
     * @return int
     */
    private function generateSearchId($pattern, $targetTable)
    {
        // @formatter:off
        $query = $this->getDependencyProvider()->getBuilder()->insert('search')
            ->fieldlist([
                'search_pattern',
                'target_table'
            ], true)
            ->values([
                [$pattern, $targetTable]
            ])
            ->getSqlQuery();
        // @formatter:off

        return $this->runWithLastId($query);
    }

    /**
     * Get the amount of rows in a search result
     *
     * @param int $searchId the id of the search
     * @return int the amount of rows in the search
     */
    public function getSearchResultCount($searchId) {
        // @formatter:off
        $query = $this->getDependencyProvider()->getBuilder()->select()
                ->field('result_count')
            ->table('search')
            ->where()
                ->field('search_id')->equals()->value($searchId)
            ->close()
            ->getSqlQuery();
        // @formatter:on

        return $this->runWithFirstKeyFirstValue($query);
    }

    private function updateResultCount($searchId, $resultCount)
    {
        // @formatter:off
        $query = $this->getDependencyProvider()->getBuilder()->update('search')
            ->updates([
                ['result_count', $resultCount]
            ])
            ->where()
                ->field('search_id')->equals()->value($searchId)
            ->close()
            ->getSqlQuery();
        // @formatter:on

        $this->runSimple($query);
    }
}
