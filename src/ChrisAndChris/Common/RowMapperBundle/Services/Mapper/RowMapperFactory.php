<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @name RowMapperFactory
 * @version
 * @since
 * @package
 * @subpackage
 * @author    Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link      http://www.klit.ch
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
