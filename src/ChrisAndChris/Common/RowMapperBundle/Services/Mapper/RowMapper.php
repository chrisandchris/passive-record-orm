<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper;

use ChrisAndChris\Common\RowMapperBundle\Entity\EmptyEntity;
use ChrisAndChris\Common\RowMapperBundle\Entity\Entity;
use ChrisAndChris\Common\RowMapperBundle\Entity\PopulateEntity;
use ChrisAndChris\Common\RowMapperBundle\Entity\StrictEntity;
use ChrisAndChris\Common\RowMapperBundle\Events\Mapping\PopulationEvent;
use ChrisAndChris\Common\RowMapperBundle\Events\MappingEvents;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\DatabaseException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\InsufficientPopulationException;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\EncryptionServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @name RowMapper
 * @version    2.0.1
 * @lastChange v2.1.0
 * @since      v1.0.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class RowMapper
{

    /**
     * @var EncryptionServiceInterface[]
     */
    private $encryptionServices = [];
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * RowMapper constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Add a new encryption ability
     *
     * @param EncryptionServiceInterface $encryptionService
     */
    public function addEncryptionAbility(EncryptionServiceInterface $encryptionService)
    {
        $this->encryptionServices[] = $encryptionService;
    }

    /**
     * Map a single result from a statement
     *
     * @param \PDOStatement $statement the statement to map
     * @param Entity        $entity    the entity to map into
     * @return Entity
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function mapSingleFromResult(\PDOStatement $statement, Entity $entity)
    {
        $list = $this->mapFromResult($statement, $entity, 1);
        if (count($list) == 0) {
            throw new NotFoundHttpException;
        }

        return $list[0];
    }

    /**
     * Maps a result from a statement into an entity
     *
     * @param \PDOStatement $statement the statement to map
     * @param Entity        $entity    the entity to use
     * @param int           $limit     max amount of rows to map
     * @return Entity[] list of mapped rows
     */
    public function mapFromResult(\PDOStatement $statement, Entity $entity = null, $limit = null)
    {
        $return = [];
        $count = 0;
        while (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC)) &&
            (++$count <= $limit || $limit === null)) {
            if ($entity === null) {
                $return[] = $this->mapRow($row);
            } else {
                $return[] = $this->mapRow($row, clone $entity);
            }
        }

        return $return;
    }

    /**
     * Map a single row by calling setter if possible or
     * accessing the properties directly if no setter available<br />
     * <br />
     * The setter name is generated by a key of $row, by following rules:<br />
     * <ul>
     *  <li>underscores are removed, next letter is uppercase</li>
     *  <li>first letter goes uppercase</li>
     *  <li>a "set" string is added to the beginning</li>
     * </ul>
     *
     * @param array $row    the single row to map
     * @param       $entity Entity entity to map to
     * @return Entity|array $entity the mapped entity
     * @throws DatabaseException if there is no such property
     * @throws InvalidOptionException if a EmptyEntity instance is given
     */
    public function mapRow(array $row, Entity $entity = null)
    {
        if ($entity instanceof EmptyEntity) {
            throw new InvalidOptionException(
                'You are not allowed to map rows to an EmptyEntity instance'
            );
        }
        if (!isset($entity)) {
            $entity = new EmptyEntity();
        }
        $this->populateFields($row, $entity);

        $entity = $this->checkForDecryption($entity);

        return $entity;
    }

    /**
     * @param array  $row
     * @param Entity $entity
     * @throws DatabaseException
     * @throws InsufficientPopulationException if strict entity is not fully populated
     */
    private function populateFields(array $row, Entity $entity)
    {
        $entityFiller = function (Entity &$entity, $field, $value) {
            $methodName = $this->buildMethodName($field);
            if (method_exists($entity, $methodName)) {
                $entity->$methodName($value);

                return 1;
            } else {
                if (property_exists($entity, $field) ||
                    $entity instanceof EmptyEntity
                ) {
                    $entity->$field = $value;

                    return 1;
                } else {
                    throw new DatabaseException(sprintf('No property %s found for Entity', $field));
                }
            }
        };

        $count = 0;
        foreach ($row as $field => $value) {
            $count += $entityFiller($entity, $field, $value);
        }

        if ($entity instanceof PopulateEntity) {
            $event = $this->eventDispatcher->dispatch(
                MappingEvents::POST_MAPPING_ROW_POPULATION,
                new PopulationEvent($entity, $entityFiller)
            );
            $count += $event->getWrittenFieldCount();
        }

        if ($entity instanceof StrictEntity && count($row) != $count) {
            throw new InsufficientPopulationException(
                sprintf(
                    'Requires entity "%s" to get populated for %d fields, but did only %d',
                    get_class($entity),
                    count($row),
                    $count
                )
            );
        }
    }

    /**
     * Build a method name
     *
     * @param $key
     * @return string
     */
    public function buildMethodName($key)
    {
        $partials = explode('_', $key);
        foreach ($partials as $idx => $part) {
            $partials[$idx] = ucfirst($part);
        }

        return 'set' . implode('', $partials);
    }

    /**
     * @param Entity $entity
     * @return array|EmptyEntity|Entity
     */
    private function checkForDecryption(Entity $entity)
    {
        $entity = $this->runDecryption($entity);

        if ($entity instanceof EmptyEntity) {
            $fields = [];
            foreach (get_object_vars($entity) as $property => $value) {
                $fields[$property] = $value;
            }
            $entity = $fields;

            return $entity;
        }

        return $entity;
    }

    /**
     * Run the decryption process
     *
     * @param Entity $entity
     * @return Entity
     */
    private function runDecryption(Entity $entity)
    {
        foreach ($this->encryptionServices as $encryptionService) {
            if ($encryptionService->isResponsible($entity)) {
                return $encryptionService->decrypt($entity);
            }
        }

        return $entity;
    }

    /**
     * Maps a statement to an associative array<br />
     * <br />
     * The closure is used to map any row, it must give back an array.<br />
     * The array <i>may</i> contain an index "key" with the desired key value
     * of the returned array and it <i>must</i> contain an index "value" with
     * the value to map
     *
     *
     * @param \PDOStatement $statement the statement to map
     * @param Entity        $entity    the entity to map from
     * @param \Closure      $callable  the callable to use to map any row
     * @return array
     * @throws InvalidOptionException if invalid input is given
     */
    public function mapToArray($statement, Entity $entity, \Closure $callable)
    {
        $array = $this->mapFromResult($statement, $entity);
        $return = [];
        foreach ($array as $row) {
            $a = $callable($row);
            if (!is_array($a)) {
                throw new InvalidOptionException('Callable must return array with at least index "value"');
            }
            if (isset($a['key']) && !empty($a['key'])) {
                $return[$a['key']] = $a['value'];
            } else {
                $return[] = $a['value'];
            }
        }

        return $return;
    }
}
