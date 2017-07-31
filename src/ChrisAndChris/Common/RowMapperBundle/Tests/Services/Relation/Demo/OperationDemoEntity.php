<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Relation\Demo;

use ChrisAndChris\Common\RowMapperBundle\Entity\Relation\RelatedEntity;

/**
 *
 *
 * @name OperationDemoEntity
 * @version   1.0.0
 * @since     1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class OperationDemoEntity extends RelatedEntity
{

    public $operationId;
    public $name;

    public function getRelations() : array
    {
        return [];
    }

    public function getLogicalName() : string
    {
        return 'operation';
    }
}
