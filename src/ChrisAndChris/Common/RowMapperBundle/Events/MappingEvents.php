<?php
namespace ChrisAndChris\Common\RowMapperBundle\Events;

/**
 * @name MappingEvents
 * @version    1.0.0
 * @since      v2.1.1
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
final class MappingEvents
{

    /**
     * After a row is mapped, this event is fired to provide additional population
     *
     * @see
     */
    const POST_MAPPING_ROW_POPULATION = 'cac.mapping.post_mapping_row_population';
}
