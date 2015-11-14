<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper;

use Symfony\Component\EventDispatcher\EventDispatcher;

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

    /** @var EventDispatcher */
    private $eventDispatcher;

    /**
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher) {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Create a new RowMapper
     *
     * @return RowMapper
     */
    public function getMapper() {
        $mapper = new RowMapper();
        /** @var NewMapperEvent $event */
        $event =
            $this->eventDispatcher->dispatch('rowMapperBundle.createNewMapper', new NewMapperEvent());
        foreach ($event->getEncryptionAbilities() as $ability) {
            $mapper->addEncryptionAbility($ability);
        }

        return $mapper;
    }
}
