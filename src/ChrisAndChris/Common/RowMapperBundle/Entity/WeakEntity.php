<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapper\ChrisAndChris\Common\RowMapperBundle\Entity;

use ChrisAndChris\Common\RowMapperBundle\Entity\Entity;

/**
 * A strict entity is an entity ignoring unknown fields
 *
 * This is the opposite of the StrictEntity
 *
 * @name StrictEntity
 * @version    1.0.0
 * @since      v2.1.1
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 *
 * @see        \ChrisAndChris\Common\RowMapperBundle\Entity\StrictEntity
 */
interface WeakEntity extends Entity
{

}
