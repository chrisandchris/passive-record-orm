<?php
namespace ChrisAndChris\Common\RowMapperBundle\Events\Transmitters;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\ClassNotFoundException;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\Mapper\MapperInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @name MapperEvent
 * @version    1.0.0
 * @since      v2.2.0
 * @lastChange v2.2.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class MapperEvent extends Event
{

    /** @var MapperInterface */
    private $mappers;

    /**
     * Add (and override) bag for the compatible systems
     *
     * @param MapperInterface $mapperInterface
     * @param array           $compatibleSystems
     */
    public function add(MapperInterface $mapperInterface, array $compatibleSystems)
    {
        foreach ($compatibleSystems as $system) {
            if (!isset($this->mappers[$system])) {
                $this->mappers[$system] = $mapperInterface;
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
    public function getMapper($desiredSystem)
    {
        if (!isset($this->mappers[$desiredSystem])) {
            throw new ClassNotFoundException(sprintf(
                'There is no bag registered for desired type "%s"',
                $desiredSystem
            ));
        }

        return $this->mappers[$desiredSystem];
    }
}
