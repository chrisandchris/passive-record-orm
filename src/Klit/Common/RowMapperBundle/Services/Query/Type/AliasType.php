<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name AliasType
 * @version v1.0.0
 * @since v2.0.0
 * @package Common
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
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
