<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name AliasType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class AliasType implements TypeInterface {

    private $alias;

    function __construct($alias) {
        $this->alias = $alias;
    }

    /**
     * @inheritdoc
     */
    function getTypeName() {
        return 'alias';
    }

    /**
     * @return mixed
     */
    public function getAlias() {
        return $this->alias;
    }
}
