<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Pdo;

use ChrisAndChris\Common\RowMapperBundle\Entity\Entity;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\DatabaseException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @name RowMapper
 * @version   2.0.0
 * @since     v1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class RowMapper {

    /**
     * Map a single result from a statement
     *
     * @param \PDOStatement $statement the statement to map
     * @param Entity        $Entity    the entity to map into
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
     * Maps a result from a statement into an entity
     *
     * @param \PDOStatement $statement the statement to map
     * @param Entity $Entity the entity to use
     * @param int    $limit  max amount of rows to map
     * @return array list of mapped rows
     */
    public function mapFromResult(\PDOStatement $statement, Entity $Entity, $limit = null) {
        $return = [];
        $c = 0;
        while (false !== ($row = $statement->fetch(\PDO::FETCH_ASSOC)) && (++$c <= $limit || $limit == null)) {
            $return[] = $this->mapRow($row, clone $Entity);
        }

        return $return;
    }

    /**
     * Map a single row by calling setter or getter methods
     *
     * @param array $row    the single row to map
     * @param       $Entity Entity entity to map to
     * @return Entity mapped entity
     * @throws DatabaseException if there is no such property
     */
    private function mapRow(array $row, Entity $Entity) {
        foreach ($row as $key => $value) {
            if (method_exists($Entity, 'set' . ucfirst($key))) {
                call_user_func([$Entity, 'set' . ucfirst($key)], $value);
            } else {
                if (property_exists($Entity, $key)) {
                    $Entity->$key = $value;
                } else {
                    throw new DatabaseException("No property '$key' found for Entity");
                }
            }
        }

        return $Entity;
    }

    /**
     * Maps a statement to an associative array<br />
     * <br />
     * The closure is used to map any row, it must give back an array.<br />
     * The array <i>may</i> contain an index "key" with the desired key value of the returned array and
     * it <i>must</i> contain an index "value" with the value to map
     *
     * @throws FatalErrorException
     * @param \PDOStatement $statement the statement to map
     * @param Entity   $entity   the entity to map from
     * @param \Closure $callable the callable to use to map any row
     * @return array the associative mapped array
     */
    public function mapToArray($statement, Entity $entity, \Closure $callable) {
        $array = $this->mapFromResult($statement, $entity);
        $return = [];
        foreach ($array as $row) {
            $a = $callable($row);
            if (!is_array($a)) {
                throw new FatalErrorException("Callable must return an array with at least index 'value'");
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
