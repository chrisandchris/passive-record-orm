<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name LikeType
 * @version   1.0.0
 * @since     2.0.0
 * @package   RowMapperBundle
 * @author    Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link      http://www.klit.ch
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
