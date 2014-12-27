<?php
namespace Klit\Common\RowMapperBundle\Services\Pdo;

use Klit\Common\RowMapperBundle\Entity\Entity;
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
     * @param \PDOStatement $statement
     * @param $Map
     * @param limit
     * @return array list of mapped rows
     */
    public function mapFromResult(\PDOStatement $statement, Entity $Map, $limit = null) {
        $return = array();
        $c = 0;
        while (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC)) && (++$c <= $limit || $limit == null)) {
            $return[] = $this->mapRow($row, clone $Map);
        }
        return $return;
    }

    /**
     * @param array $row the single row to map
     * @param $Map Entity entity to map to
     * @return Entity mapped entity
     */
    private function mapRow(array $row, Entity $Map) {
        foreach ($row as $key => $value) {
            if (property_exists($Map, $key) || method_exists($Map, 'set' . ucfirst($key))) {
                call_user_func(array($Map, 'set' . ucfirst($key)), $value);
            } else {
                // @todo should we throw an exception?
                $Map->$key = $value;
            }
        }
        return $Map;
    }

    /**
     * @param \PDOStatement $statement
     * @param $param
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function mapSingleFromResult(\PDOStatement $statement, Entity $param) {
        $list = $this->mapFromResult($statement, $param, 1);
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
     * @param $statement \PDOStatement the statement to map
     * @param $entity Entity the entity to map from
     * @param $closure \Closure the closure to use to map any row
     * @return array the associative mapped array
     */
    public function mapToArray($statement, Entity $entity, $closure) {
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
