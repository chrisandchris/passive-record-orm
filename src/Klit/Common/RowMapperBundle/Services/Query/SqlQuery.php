<?php
namespace Klit\Common\RowMapperBundle\Services\Query;
/**
 * @name SqlStatement
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class SqlQuery {
    private $query;
    private $parameters;

    function __construct($query, $paremeters) {
        $this->query = $query;
        $this->parameters = $paremeters;
    }

    /**
     * @return mixed
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * @return mixed
     */
    public function getParameters() {
        return $this->parameters;
    }
}
