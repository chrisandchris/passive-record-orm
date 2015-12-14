<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Utilities;

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

    public function setMappingRepository($repository) {
        $this->repository = $repository;
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
        return $this->getDependencyProvider()->getBuilder()->in()->brace()
            ->select()
                ->field($this->repository->getPrimaryKeyOfTable($table))
            ->table('search_' . $table)
            ->where()
                ->field('search_id')->equals()->value($searchId)
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
     * Get the amount of rows in a search result
     *
     * @param int $searchId the id of the search
     * @return int the amount of rows in the search
     */
    public function getSearchResultCount($searchId) {
        // @formatter:off
        $query = $this->getDependencyProvider()->getBuilder()->select()
            ->f('count')->field('search_id')->close()
            ->table('search')
            ->where()
                ->field('search_id')->equals()->value($searchId)
            ->close()
            ->getSqlQuery();
        // @formatter:on

        return $this->runWithFirstKeyFirstValue($query);
    }

    /**
     * Generates a new unique search id
     *
     * @param $pattern
     * @return int
     */
    public function generateSearchId($pattern) {
        // @formatter:off
        $query = $this->getDependencyProvider()->getBuilder()->insert('search')
            ->fieldlist([
                'search_pattern'
            ], true)
            ->values([
                [$pattern]
            ])
            ->getSqlQuery();
        // @formatter:off

        return $this->runWithLastId($query);
    }
}
