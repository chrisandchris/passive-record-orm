<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Utilities;

use ChrisAndChris\Common\RowMapperBundle\Services\Model\Model;

/**
 * @name Listable
 * @version
 * @since
 * @package
 * @subpackage
 * @author    Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link      http://www.klit.ch
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
