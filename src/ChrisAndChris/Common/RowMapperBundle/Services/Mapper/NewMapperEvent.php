<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper;

use Symfony\Component\EventDispatcher\Event;

/**
 * @name NewMapperEvent
 * @version    1.0.0
 * @lastChange v2.1.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       http://www.klit.ch
 */
class NewMapperEvent extends Event {

    /** @var [] */
    private $encryptionAbilities = [];

    /**
     * Add a new encryption ability
     */
    public function addEncryptionAbility() {
        $this->encryptionAbilities = [];
    }

    /**
     * Get all added encryption abilities
     *
     * @return array
     */
    public function getEncryptionAbilities() {
        return $this->encryptionAbilities;
    }
}
