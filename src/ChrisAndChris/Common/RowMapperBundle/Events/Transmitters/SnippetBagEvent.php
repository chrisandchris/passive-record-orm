<?php

namespace ChrisAndChris\Common\RowMapperBundle\Events\Transmitters;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\ClassNotFoundException;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\BagInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @name SnippetBagEvent
 * @version   1.0.0
 * @since     v2.2.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class SnippetBagEvent extends Event
{

    /** @var BagInterface */
    private $bags = [];

    /**
     * Add (and override) bag for the compatible systems
     *
     * @param BagInterface $bag
     * @param array        $compatibleSystems
     */
    public function add(BagInterface $bag, array $compatibleSystems)
    {
        foreach ($compatibleSystems as $system) {
            if (!isset($this->bags[$system])) {
                $this->bags[$system] = $bag;
            }
        }
    }

    /**
     * Get the bag for a specific system
     *
     * @param $desiredSystem
     * @return mixed
     * @throws ClassNotFoundException
     */
    public function getBag($desiredSystem)
    {
        if (!isset($this->bags[$desiredSystem])) {
            throw new ClassNotFoundException(sprintf(
                'There is no bag registered for desired type "%s", we have [%s]',
                $desiredSystem,
                implode(', ', array_keys($this->bags))
            ));
        }

        return $this->bags[$desiredSystem];
    }
}
