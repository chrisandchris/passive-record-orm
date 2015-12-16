<?php
namespace ChrisAndChris\Common\RowMapperBundle\Entity\Search;

use ChrisAndChris\Common\RowMapperBundle\Entity\Mapping\Field;
use ChrisAndChris\Common\RowMapperBundle\Entity\Mapping\Relation;

/**
 * @name SearchContainer
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class SearchContainer {

    /** @var string the primary key of the base table */
    public $primaryKey;
    /** @var string the table to save the search */
    public $targetTable;
    /** @var string the base table to start search with */
    private $rootTable;
    /** @var Relation[] an array of joins to execute */
    private $joinedTables = null;
    /** @var Field[] an array of fields to search within */
    private $lookupFields = null;
    /** @var FilterCondition[] an array of conditions which must apply to the search */
    private $filterConditions = [];
    /** @var string the term to search for */
    private $term;
    /** @var int previous search id */
    private $searchId;

    /**
     * @param string $rootTable
     * @param string $term
     * @param int    $searchId
     */
    public function __construct($rootTable, $term, $searchId = null)
    {
        $this->rootTable = $rootTable;
        $this->term = $term;
        $this->searchId = $searchId;
    }

    /**
     * @param Relation $relation
     */
    public function addJoin(Relation $relation)
    {
        $this->joinedTables[] = $relation;
    }

    /**
     * @return Relation[]
     */
    public function getJoinedTables()
    {
        return $this->joinedTables;
    }

    /**
     * @return string
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @return string
     */
    public function getRootTable()
    {
        return $this->rootTable;
    }

    /**
     * @return int
     */
    public function getSearchId()
    {
        return $this->searchId;
    }

    public function addLookup(Field $lookup)
    {
        $this->lookupFields[] = $lookup;
    }

    /**
     * @return Field[]
     */
    public function getLookupFields()
    {
        return $this->lookupFields;
    }

    /**
     * @return FilterCondition[]
     */
    public function getFilterConditions()
    {
        return $this->filterConditions;
    }

    /**
     * @param FilterCondition $condition
     */
    public function addFilterCondition(FilterCondition $condition)
    {
        $this->filterConditions[] = $condition;
    }
}
