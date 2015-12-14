<?php
namespace ChrisAndChris\Common\RowMapperBundle\Entity\Search;

/**
 * @name SearchContainer
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class SearchContainer {

    /** @var string the base table to start search with */
    public $rootTable;
    /** @var string the primary key of the base table */
    public $primaryKey;
    /** @var string the table to save the search */
    public $targetTable;
    /** @var array an array of joins to execute */
    public $joinedTables = null;
    /** @var array an array of fields to search within */
    public $lookupFields = null;
    /** @var array an array of conditions which must apply to the search */
    public $filterConditions = [];
    /** @var string the term to search for */
    public $term;
    /** @var int previous search id */
    public $searchId = null;
}
