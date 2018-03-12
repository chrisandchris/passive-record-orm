<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Events\Process;

/**
 *
 *
 * @name \ChrisAndChris\Common\RowMapperBundle\Events\Process\ProcessOutEvent
 * @version   1.0.0
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class ProcessOutEvent extends ProcessEvent
{

    /**
     * @var mixed
     */
    private $result;

    public function __construct(string $shortName, string $function, $result)
    {
        parent::__construct($shortName, $function);
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
}
