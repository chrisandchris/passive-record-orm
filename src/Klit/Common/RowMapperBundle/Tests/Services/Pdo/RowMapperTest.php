<?php
namespace Klit\Common\RowMapperBundle\Tests\Services\Pdo;

use Klit\Common\RowMapperBundle\Entity\Entity;
use Klit\Common\RowMapperBundle\Services\Pdo\RowMapper;
use Klit\Common\RowMapperBundle\Tests\TestKernel;
use PDO;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @name RowMapperTest
 * @version 1.0.0
 * @package CommonRowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class RowMapperTest extends TestKernel {

    public function getStatementMockup() {
        PdoStatement::$id = 0;
        return new PdoStatement();
    }

    public function getRowMapper() {
        return new RowMapper();
    }

    public function testMapFromResult() {
        $Mapper = new RowMapper();
        PdoStatement::$maxId = 2;
        $rows = $Mapper->mapFromResult($this->getStatementMockup(), new DummyEntity());

        /** @var DummyEntity $Row */
        $Row = $rows[0];
        $this->assertEquals(1, $Row->getId());
        $this->assertEquals('Name 1', $Row->getName());

        $Row = $rows[1];
        $this->assertEquals(2, $Row->getId());
        $this->assertEquals('Name 2', $Row->getName());
    }

    public function testMapFromResultLimit() {
        $Mapper = new RowMapper();
        PdoStatement::$maxId = 100;
        $rows = $Mapper->mapFromResult($this->getStatementMockup(), new DummyEntity(), 10);

        $this->assertEquals(10, count($rows));
    }

    public function testEntity() {
        $Mapper = new RowMapper();
        PdoStatement::$maxId = 1;
        $rows = $Mapper->mapFromResult($this->getStatementMockup(), new WrongDummyEntity());

        $this->assertEquals('Name 1', $rows[0]->name);
    }

    public function testSingle() {
        $Mapper = new RowMapper();
        PdoStatement::$maxId = 2;
        $row = $Mapper->mapSingleFromResult($this->getStatementMockup(), new DummyEntity());

        $this->assertTrue(is_object($row));
    }

    public function testNotFoundSingle() {
        $Mapper = new RowMapper();
        PdoStatement::$maxId = 0;
        try {
            $row = $Mapper->mapSingleFromResult($this->getStatementMockup(), new DummyEntity());
            $this->fail('Must throw not found exception due to empty result');
        } catch (NotFoundHttpException $e) {
            // ignore it
        }
    }

    public function testMapToArray() {
        $Mapper = new RowMapper();
        PdoStatement::$maxId = 2;
        $array = $Mapper->mapToArray($this->getStatementMockup(), new DummyEntity(), function(DummyEntity $entity) {
            return array(
                'key' => $entity->getId(),
                'value' => $entity->getName()
            );
        });

        $this->assertEquals($array[1], 'Name 1');
        $this->assertEquals($array[2], 'Name 2');
        $this->assertEquals(2, count($array));
    }

    public function testMapToArrayErrors() {
        $Mapper = new RowMapper();
        PdoStatement::$maxId = 2;
        try{
            $array = $Mapper->mapToArray($this->getStatementMockup(), new DummyEntity(), function(DummyEntity $entity) {
                return null;
            });
            $this->fail('Must fail due to wrong response of closure');
        } catch (FatalErrorException $e) {
            // ignore
        }
    }

    public function testMapToArrayImplicitKey() {
        $Mapper = new RowMapper();
        PdoStatement::$maxId = 2;
        $array = $Mapper->mapToArray($this->getStatementMockup(), new DummyEntity(), function(DummyEntity $entity) {
            return array(
                'value' => $entity->getName()
            );
        });

        $this->assertEquals($array[0], 'Name 1');
        $this->assertEquals($array[1], 'Name 2');
        $this->assertEquals(2, count($array));
    }
}

class PdoStatement extends \PDOStatement {
    public static $id = 0;
    public static $maxId = 5;

    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0) {
        if (self::$id >= self::$maxId) {
            return false;
        }
        self::$id++;
        return array(
            'id' => self::$id,
            'name' => 'Name ' . self::$id
        );
    }
}

class DummyEntity implements Entity {
    private $id;
    private $name;

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }
}

class WrongDummyEntity implements Entity {
    private $id;

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }
}
