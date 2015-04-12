<?php
namespace ChrisAndChris\Common\RowMapperBundle\Entity;
/**
 * @name KeyValueEntity
 * @version 1.0.0
 * @since v2.0.0
 * @package KlitCommon
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class KeyValueEntity implements Entity {
    /** @var mixed the key */
    public $key;
    /** @var mixed the value */
    public $value;
}
