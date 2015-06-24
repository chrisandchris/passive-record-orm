<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name LikeType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class LikeType implements ParameterizedTypeInterface {

    /** @var string */
    private $pattern;

    function __construct($pattern = '%') {
        $this->pattern = $pattern;
    }

    function getParameter($index) {
        return $this->pattern;
    }

    /**
     * @inheritdoc
     */
    function getTypeName() {
        return 'like';
    }
}
