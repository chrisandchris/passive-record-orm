<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name LimitType
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class LimitType implements TypeInterface {
    private $limit;

    function __construct($limit = 1) {
        $this->limit = $limit;
    }

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'limit';
    }

    /**
     * @return mixed
     */
    public function getLimit() {
        return $this->limit;
    }
}
