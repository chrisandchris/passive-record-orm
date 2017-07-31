<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Entity\Relation;

/**
 *
 *
 * @name RelatedEntityInterface
 * @version   1.0.0
 * @since     1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
interface RelatedEntityInterface
{

    public function getLogicalName() : string;
}
