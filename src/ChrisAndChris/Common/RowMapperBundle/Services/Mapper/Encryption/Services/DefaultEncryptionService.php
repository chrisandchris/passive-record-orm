<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\Services;

use ChrisAndChris\Common\RowMapperBundle\Entity\Entity;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\EncryptionExecutorInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\EncryptionServiceInterface;

/**
 * @name DefaultEncryptionService
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class DefaultEncryptionService implements EncryptionServiceInterface {

    /** @var EncryptionExecutorInterface[] */
    private $fieldExecutors;
    /** @var EncryptionExecutorInterface */
    private $rowExecutor;
    /** @var [] */
    private $responsibilities = [];

    /**
     * @inheritdoc
     */
    public function useForRow(EncryptionExecutorInterface $executor, array $fields = null) {
        $this->rowExecutor = [
            'executor'       => $executor,
            'disallowFields' => $fields,
        ];
    }

    /**
     * @inheritDoc
     */
    public function useForField($field, EncryptionExecutorInterface $executor) {
        if (!is_array($field)) {
            $field = [$field];
        }
        foreach ($field as $singleField) {
            $this->fieldExecutors[$singleField] = $executor;
        }
    }

    /**
     * @inheritDoc
     */
    public function encrypt(Entity &$entity) {
        foreach (array_keys(get_object_vars($entity)) as $property) {
            $this->run($entity, $property, 'encrypt');
        }

        return $entity;
    }

    private function run(&$entity, $property, $mode) {
        if (isset($this->fieldExecutors[$property])) {
            $entity->$property =
                $this->fieldExecutors[$property]->$mode($entity->$property);

            return $entity;
        }

        if ($this->rowExecutor !== null) {
            if (!in_array($property, $this->rowExecutor['disallowFields']) ||
                $this->rowExecutor['disallowFields'] === null
            ) {
                $entity->$property =
                    $this->rowExecutor['executor']->$mode($entity->$property);
            }

            return $entity;
        }

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function decrypt(Entity &$entity) {
        foreach (array_keys(get_object_vars($entity)) as $property) {
            $this->run($entity, $property, 'decrypt');
        }

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function isResponsible(Entity $entity) {
        return in_array(get_class($entity), $this->responsibilities);
    }

    /**
     * @inheritDoc
     */
    public function makeResponsible(Entity $entity) {
        $this->responsibilities[] = get_class($entity);
    }
}
