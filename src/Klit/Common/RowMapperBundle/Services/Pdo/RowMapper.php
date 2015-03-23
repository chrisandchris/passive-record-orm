<?php
namespace Klit\Common\RowMapperBundle\Services\Pdo;

use Klit\Common\RowMapperBundle\Entity\Entity;
use Klit\Common\RowMapperBundle\Entity\ManagedEntity;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @name RowMapper
 * @version 1.0.0
 * @package CommonRowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class RowMapper {

    /**
     * Maps a result from a statement into an entity
     *
     * @param \PDOStatement $statement the statement to map
     * @param Entity $Entity the entity to use
     * @param int $limit max amount of rows to map
     * @return array list of mapped rows
     */
    public function mapFromResult(\PDOStatement $statement, Entity $Entity, $limit = null) {
        $return = array();
        $c = 0;
        while (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC)) && (++$c <= $limit || $limit == null)) {
            $return[] = $this->mapRow($row, clone $Entity);
        }
        return $return;
    }

    public function map(\PDOStatement $statement, Entity $Entity, array $fields) {
        $return = [];
        while (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $return[]= $this->mapRow($row, clone $Entity, $fields);
        }
    }

    /**
     * Map a single row by calling setter or getter methods
     *
     * @param array $row the single row to map
     * @param $Entity Entity entity to map to
     * @param array $fields the fields that are mapped
     * @return Entity mapped entity
     */
    private function mapRow(array $row, Entity $Entity, array $fields = null) {
        foreach ($row as $key => $value) {
            if (property_exists($Entity, $key) || method_exists($Entity, 'set' . ucfirst($key))) {
                call_user_func(array($Entity, 'set' . ucfirst($key)), $value);
            } else {
                // @todo should we throw an exception?
                $Entity->$key = $value;
            }
        }
        return $Entity;
    }

    /**
     * Map a single result from a statement
     *
     * @param \PDOStatement $statement the statement to map
     * @param Entity $Entity the entity to map into
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function mapSingleFromResult(\PDOStatement $statement, Entity $Entity) {
        $list = $this->mapFromResult($statement, $Entity, 1);
        if (count($list) == 0) {
            throw new NotFoundHttpException;
        }
        return $list[0];
    }

    /**
     * Maps a statement to an associative array
     *
     * The closure is used to map any row, it must give back an array
     * The array may contain an index "key" with the key of the associative array returned by the method
     * It must contain an index "value" with the value to map
     *
     * @throws FatalErrorException
     * @param \PDOStatement $statement the statement to map
     * @param Entity $entity the entity to map from
     * @param \Closure $closure the closure to use to map any row
     * @return array the associative mapped array
     */
    public function mapToArray($statement, Entity $entity, \Closure $closure) {
        $array = $this->mapFromResult($statement, $entity);
        $return = array();
        foreach ($array as $row) {
            $a = $closure($row);
            if (!is_array($a)) {
                throw new FatalErrorException("This ist not an array");
            }
            if (isset($a['key'])) {
                $return[$a['key']] = $a['value'];
            } else {
                $return[] = $a['value'];
            }
        }
        return $return;
    }
}
