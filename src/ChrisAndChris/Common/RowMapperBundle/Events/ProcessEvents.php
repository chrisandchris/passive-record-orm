<?php
/**
 * Created by PhpStorm.
 * User: christian
 * Date: 04.02.18
 * Time: 13:20
 */

namespace ChrisAndChris\Common\RowMapperBundle\Events;

class ProcessEvents
{

    /**
     * Fired before the process is run
     */
    const ON_IN  = 'chrisandchris.orm.process.on_in';
    /**
     * Fired after the process is (successfully) run
     */
    const ON_OUT = 'chrisandchris.orm.process.on_out';
}
