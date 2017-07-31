<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Relation\Demo;

use ChrisAndChris\Common\RowMapperBundle\Entity\Relation\RelatedEntity;
use ChrisAndChris\Common\RowMapperBundle\Entity\Relation\Relation;

/**
 *
 *
 * @name InvoiceDemoEntity
 * @version   1.0.0
 * @since     1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class InvoiceDemoEntity extends RelatedEntity
{

    public $invoiceId;
    public $customerId;

    public function getRelations() : array
    {
        return [
            new Relation(OperationDemoEntity::class, 'operation_id'),
        ];
    }

    public function getLogicalName() : string
    {
        return 'invoice';
    }

}
