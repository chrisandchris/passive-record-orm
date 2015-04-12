<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name OffsetType
 * @version 1.0.0
 * @since v2.0.0
 * @package Common
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class OffsetType implements TypeInterface {
    private $offset;

    function __construct($offset) {
        $this->offset = (int)$offset;
    }

    /**
     * @inheritdoc
     */
    function getTypeName() {
        return 'offset';
    }

    /**
     * @return mixed
     */
    public function getOffset() {
        return $this->offset;
    }
}
