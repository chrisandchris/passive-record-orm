<?php
/**
 * Created by PhpStorm.
 * User: christian
 * Date: 04.02.18
 * Time: 13:20
 */

namespace ChrisAndChris\Common\RowMapperBundle\Events;

final class ProcessEvents
{

    /**
     * Fired before the process is run
     *
     * @see \ChrisAndChris\Common\RowMapperBundle\Events\Process\ProcessEvent
     */
    const ON_IN  = 'chrisandchris.orm.process.on_in';
    /**
     * Fired after the process is (successfully) run
     *
     * @see \ChrisAndChris\Common\RowMapperBundle\Events\Process\ProcessOutEvent
     */
    const ON_OUT = 'chrisandchris.orm.process.on_out';
}
