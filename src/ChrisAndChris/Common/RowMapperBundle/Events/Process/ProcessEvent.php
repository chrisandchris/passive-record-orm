<?php
/**
 * Created by PhpStorm.
 * User: christian
 * Date: 04.02.18
 * Time: 13:22
 */

namespace ChrisAndChris\Common\RowMapperBundle\Events\Process;

use Symfony\Component\EventDispatcher\Event;

class ProcessEvent extends Event
{

    /**
     * @var string
     */
    private $shortName;
    /**
     * @var string
     */
    private $function;

    /**
     * ProcessEvent constructor.
     *
     * @param string $shortName
     * @param string $function
     */
    public function __construct(string $shortName, string $function)
    {
        $this->shortName = $shortName;
        $this->function = $function;
    }

    /**
     * @return string
     */
    public function getShortName() : string
    {
        return $this->shortName;
    }

    /**
     * @return string
     */
    public function getFunction() : string
    {
        return $this->function;
    }
}
