<?php

namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Mapper;

use ChrisAndChris\Common\RowMapperBundle\Entity\Entity;
use ChrisAndChris\Common\RowMapperBundle\Entity\WeakEntity;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\DatabaseException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\Executors\StringBasedExecutor;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\Services\DefaultEncryptionService;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\Wrappers\PhpSeclibAesWrapper;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapper;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\TypeCaster;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;
use PDO;
use phpseclib\Crypt\AES;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @name RowMapperTest
 * @version   1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class RowMapperTest extends TestKernel
{

    public function testMapFromResult()
    {
        $mapper = $this->getRowMapper();

        PdoStatementDummy::$maxId = 2;
        $rows =
            $mapper->mapFromResult($this->getStatementMockup(),
                new DummyEntity());

        /** @var DummyEntity $entity */
        $entity = $rows[0];
        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('Name 1', $entity->getName());

        $entity = $rows[1];
        $this->assertEquals(2, $entity->getId());
        $this->assertEquals('Name 2', $entity->getName());
    }

    private function getEventDispatcherMock()
    {
        $mock =
            $this->getMockBuilder('\Symfony\Component\EventDispatcher\EventDispatcher')
                 ->disableOriginalConstructor()
                 ->getMock();

        return $mock;
    }

    public function getStatementMockup($mode = 'regular')
    {
        PdoStatementDummy::$id = 0;

        return new PdoStatementDummy($mode);
    }

    public function testMapFromResultLimit()
    {
        $mapper = $this->getRowMapper();

        PdoStatementDummy::$maxId = 100;
        $rows =
            $mapper->mapFromResult($this->getStatementMockup(),
                new DummyEntity(), 10);

        $this->assertEquals(10, count($rows));
    }

    public function testEntity_noSuchProperty()
    {
        $mapper = $this->getRowMapper();

        PdoStatementDummy::$maxId = 1;
        try {
            $mapper->mapFromResult(
                $this->getStatementMockup(),
                new WrongDummyEntity()
            );
            $this->fail('Must fail due to no such property');
        } catch (DatabaseException $exception) {
            // ignore
        }
    }

    public function testEntity_noSuchProperty_WeakEntity()
    {
        $mapper = $this->getRowMapper();

        PdoStatementDummy::$maxId = 1;
        try {
            $mapper->mapFromResult(
                $this->getStatementMockup(),
                new WrongDummyWeakEntity()
            );
        } catch (DatabaseException $exception) {
            $this->fail('Must NOT fail because WeakEntity is used');
        }
    }

    public function testSingle()
    {
        $mapper = $this->getRowMapper();

        PdoStatementDummy::$maxId = 2;
        $row =
            $mapper->mapSingleFromResult($this->getStatementMockup(),
                new DummyEntity());

        $this->assertTrue(is_object($row));
    }

    public function testNotFoundSingle()
    {
        $mapper = $this->getRowMapper();

        PdoStatementDummy::$maxId = 0;
        try {
            $mapper->mapSingleFromResult($this->getStatementMockup(),
                new DummyEntity());
            $this->fail('Must throw not found exception due to empty result');
        } catch (NotFoundHttpException $e) {
            // ignore it
        }
    }

    public function testMapToArray()
    {
        $mapper = $this->getRowMapper();

        PdoStatementDummy::$maxId = 2;
        $array = $mapper->mapToArray(
            $this->getStatementMockup(), new DummyEntity(),
            function (DummyEntity $entity) {
                return [
                    'key'   => $entity->getId(),
                    'value' => $entity->getName(),
                ];
            }
        );

        $this->assertEquals($array[1], 'Name 1');
        $this->assertEquals($array[2], 'Name 2');
        $this->assertEquals(2, count($array));
    }

    public function testMapToArrayErrors()
    {
        $mapper = $this->getRowMapper();

        PdoStatementDummy::$maxId = 2;
        try {
            $mapper->mapToArray(
                $this->getStatementMockup(), new DummyEntity(), function () {
                return null;
            }
            );
            $this->fail('Must fail due to wrong response of closure');
        } catch (InvalidOptionException $e) {
            // ignore
        }
    }

    public function testMapToArrayImplicitKey()
    {
        $mapper = $this->getRowMapper();

        PdoStatementDummy::$maxId = 2;
        $array = $mapper->mapToArray(
            $this->getStatementMockup(), new DummyEntity(),
            function (DummyEntity $entity) {
                return [
                    'value' => $entity->getName(),
                ];
            }
        );

        $this->assertEquals($array[0], 'Name 1');
        $this->assertEquals($array[1], 'Name 2');
        $this->assertEquals(2, count($array));
    }

    public function testBuildMethodName()
    {
        $mapper = $this->getRowMapper();

        $this->assertEquals('setName', $mapper->buildMethodName('name'));
        $this->assertEquals('setSomeName',
            $mapper->buildMethodName('some_name'));
        $this->assertEquals('setSomeName',
            $mapper->buildMethodName('someName'));
        $this->assertEquals('setSomeOtherName',
            $mapper->buildMethodName('some_other_name'));
        $this->assertEquals('setSomeOtherName',
            $mapper->buildMethodName('someOtherName'));
    }

    public function testEncryptedEntity()
    {
        $mapper = $this->getRowMapper();

        $encryptionService = new DefaultEncryptionService();
        $executor = new StringBasedExecutor(new PhpSeclibAesWrapper(new AES()));
        $executor->useKey('root', 'abc-def-def-efg-ahb');

        $encryptionService->useForField('name', $executor);
        $encryptionService->makeResponsible(new EncryptedDummyEntity());
        $mapper->addEncryptionAbility($encryptionService);

        $statement = $this->getStatementMockup('encrypted');
        $statement::$maxId = 1;
        $entities =
            $mapper->mapFromResult($statement, new EncryptedDummyEntity());
        $this->assertEquals(1, count($entities));
    }

    public function getRowMapper()
    {
        /** @noinspection PhpParamsInspection */
        return new RowMapper(
            $this->getEventDispatcherMock(),
            new TypeCaster()
        );
    }
}

class PdoStatementDummy extends \PDOStatement
{

    public static $mode  = 'regular';
    public static $id    = 0;
    public static $maxId = 5;

    /**
     * PdoStatementDummy constructor.
     *
     * @param string $mode
     */
    public function __construct($mode = 'regular')
    {
        self::$mode = $mode;
    }

    public function fetch(
        $fetch_style = null,
        $cursor_orientation = PDO::FETCH_ORI_NEXT,
        $cursor_offset = 0
    ) {
        if (self::$id >= self::$maxId) {
            return false;
        }
        self::$id++;

        if (self::$mode == 'encrypted') {
            return [
                'id'   => self::$id,
                'name' => 'def5020043c2db90977e073bb3733481a6d8a42e9c9c5d66e7963a9d'
                    . 'c4f4cc48b61547e3fe8c2ffadb5060b46ed0f6ad9b1d2837ab'
                    . '858b9e855ac8d747a58450172faf65513c039f06241efaa',
            ];
        }

        return [
            'id'   => self::$id,
            'name' => 'Name ' . self::$id,
        ];
    }
}

class DummyEntity implements Entity
{

    public $id;
    public $name;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}

class EncryptedDummyEntity extends DummyEntity
{

    public $name;
}

class WrongDummyEntity implements Entity
{

    public $id;
}

class WrongDummyWeakEntity implements WeakEntity
{

    public $id;
}
