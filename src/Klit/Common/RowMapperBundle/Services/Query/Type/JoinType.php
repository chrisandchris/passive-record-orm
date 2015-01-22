<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;
/**
 * @name JoinType
 * @version
 * @package
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class JoinType implements TypeInterface {
    /**
     * @inheritdoc
     */
    function getTypeName() {
        return 'join';
    }

    /**
     * @inheritdoc
     */
    function getAllowedChildren() {
        return array(

        );
    }

    /**
     * Generic call method
     *
     * @param mixed $data
     */
    function call($data) {
        // TODO: Implement call() method.
    }
}
