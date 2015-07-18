<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\MalformedQueryException;

/**
 * @name RawType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperbundle
 * @author ChrisAndChris
 * @link   https://github.com/chrisandchris/symfony-rowmapper
 */
class RawType implements ParameterizedTypeInterface {

    /** @var string */
    protected $raw;
    /** @var array */
    protected $params;

    function __construct($raw, array $params = []) {
        $this->raw = $raw;
        $this->params = $params;
    }

    function getParameter($index) {
        if (isset($this->params[$index])) {
            return $this->params[$index];
        }
        throw new MalformedQueryException("No parameter at index " . $index . " found");
    }

    /**
     * @inheritdoc
     */
    function getTypeName() {
        return 'raw';
    }

    /**
     * @return string
     */
    public function getRaw() {
        return $this->raw;
    }
}
