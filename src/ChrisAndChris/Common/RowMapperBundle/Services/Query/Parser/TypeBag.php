<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\MissingParameterException;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\BagInterface;

/**
 * @name ParameterBag
 * @version    1.1.0
 * @since      v2.0.2
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class TypeBag implements BagInterface
{

    /** @var array */
    private $types;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->types = [
            'alias'      => [
                'params'   => [
                    'alias',
                ],
                'required' => [
                    'alias',
                ],
            ],
            'and'        => [
                'params' => [],
            ],
            'any'        => [
                'params' => [],
            ],
            'brace'      => [
                'params' => [],
            ],
            'close'      => [
                'params' => [],
            ],
            'cast'       => [
                'params' => ['cast'],
            ],
            'comma'      => [
                'params' => [],
            ],
            'comparison' => [
                'params'   => [
                    'comparison',
                ],
                'required' => [
                    'comparison',
                ],
            ],
            'delete'     => [
                'params'   => [
                    'table',
                ],
                'required' => [
                    'table',
                ],
            ],
            'equals'     => [
                'params' => [],
            ],
            'fieldlist'  => [
                'params'   => [
                    'fields',
                ],
                'required' => [
                    'fields',
                ],
            ],
            'field'      => [
                'params'   => [
                    'identifier',
                ],
                'required' => [
                    'identifier',
                ],
            ],
            'function'   => [
                'params'   => [
                    'name',
                ],
                'required' => [
                    'name',
                ],
            ],
            'group'      => [
                'params' => [],
            ],
            'in'         => [
                'params'   => [
                    'in',
                ],
                'required' => [
                ],
            ],
            'insert'     => [
                'params'   => [
                    'table',
                    'mode',
                ],
                'required' => [
                    'table',
                ],
            ],
            'isnull'     => [
                'params' => [
                    'isnull',
                ],
            ],
            'join'       => [
                'params'   => [
                    'table',
                    'type',
                    'alias',
                ],
                'required' => [
                    'table',
                ],
            ],
            'like'       => [
                'params'   => [
                    'pattern',
                ],
                'required' => [
                    'pattern',
                ],
            ],
            'limit'      => [
                'params'   => [
                    'limit',
                ],
                'required' => [
                    'limit',
                ],
            ],
            'null'       => [
                'params' => [],
            ],
            'offset'     => [
                'params'   => [
                    'offset',
                ],
                'required' => [
                    'offset',
                ],
            ],
            'on'         => [
                'params' => [],
            ],
            'orderby'    => [
                'params'   => [
                    'field',
                    'direction',
                ],
                'required' => [
                    'field',
                    'direction',
                ],
            ],
            'order'      => [
                'params' => [],
            ],
            'or'         => [
                'params' => [],
            ],
            'raw'        => [
                'params'   => [
                    'raw',
                    'params',
                ],
                'required' => [
                    'raw',
                    'params',
                ],
            ],
            'select'     => [
                'params' => [],
            ],
            'table'      => [
                'params'   => [
                    'table',
                    'alias',
                ],
                'required' => [
                    'table',
                ],
            ],
            'union'      => [
                'params' => [
                    'mode',
                ],
            ],
            'update'     => [
                'params'   => [
                    'table',
                ],
                'required' => [
                    'table',
                ],
            ],
            'using'      => [
                'params'   => [
                    'field',
                ],
                'required' => [
                    'field',
                ],
            ],
            'values'     => [
                'params' => [],
            ],
            'value'      => [
                'params'   => [
                    'value',
                ],
                'required' => [
                    'value',
                ],
            ],
            'where'      => [
                'params' => [],
            ],
        ];
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return void
     * @throws InvalidOptionException if array is invalid
     */
    function set($name, $value)
    {
        if (!isset($value['params']) && !is_array(($value['params']))) {
            throw new InvalidOptionException(
                'You must set index params as array for "' . $name . '"'
            );
        }
        $this->types[$name] = $value;
    }

    /**
     * @param $name
     * @return array
     * @throws MissingParameterException
     */
    public function get($name)
    {
        if (!isset($this->types[$name])) {
            throw new MissingParameterException('No such type known');
        }

        return $this->types[$name];
    }

    /**
     * @inheritDoc
     */
    function getAll()
    {
        return $this->types;
    }

    /**
     * @inheritDoc
     */
    function has($name)
    {
        return isset($this->types[$name]);
    }


}
