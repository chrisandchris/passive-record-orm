<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @name RowMapperFactory
 * @version    1.0.0
 * @lastChange v2.1.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class RowMapperFactory {

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher) {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Create a new RowMapper
     *
     * @return RowMapper
     */
    public function getMapper() {
        $mapper = new RowMapper(
            $this->eventDispatcher
        );
        /** @var NewMapperEvent $event */
        $event =
            $this->eventDispatcher->dispatch('rowMapperBundle.createNewMapper', new NewMapperEvent());
        foreach ($event->getEncryptionAbilities() as $ability) {
            $mapper->addEncryptionAbility($ability);
        }

        return $mapper;
    }
}
