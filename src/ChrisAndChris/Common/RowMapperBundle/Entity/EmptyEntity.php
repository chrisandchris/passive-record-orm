<?php
namespace ChrisAndChris\Common\RowMapperBundle\Entity;

use ChrisAndChris\Common\RowMapperBundle\Entity\Mapping\Relation;

/**
 * @name EmptyEntity
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class EmptyEntity implements Entity {

    public function getRelations()
    {
        return [
            new Relation(),
        ];
    }
}
