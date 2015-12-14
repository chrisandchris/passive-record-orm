<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Utilities;

use ChrisAndChris\Common\RowMapperBundle\Services\Model\Model;

/**
 * @name Listable
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class Listable extends Model {

    /** @var int */
    private $offset = 0;
    /** @var int */
    private $limit = 30;

    public function setOffset($offset) {
        $this->offset = $offset;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
    }

    public function showList($table, array $requestedRelations, array $filters) {
    }
}
