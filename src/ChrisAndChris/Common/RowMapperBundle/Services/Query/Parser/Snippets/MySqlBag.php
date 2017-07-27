<?php

namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\Snippets;

use ChrisAndChris\Common\RowMapperBundle\Events\RowMapperEvents;
use ChrisAndChris\Common\RowMapperBundle\Events\Transmitters\SnippetBagEvent;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\MalformedQueryException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\TypeNotFoundException;

/**
 * @name MySqlBag
 * @version    1.1.0
 * @since      v2.0.2
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class MySqlBag extends AbstractBag implements SnippetBagInterface
{

    /** @var array */
    private $snippets = [];
    const DELIMITER = '`';

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->snippets = [
            'select'         => function () {
                return [
                    'code'   => 'SELECT',
                    'params' => null,
                ];
            },
            'alias'          => function (array $params) {
                return [
                    'code'   => 'as `' . $params['alias'] . '`',
                    'params' => null,
                ];
            },
            'and'            => function () {
                return [
                    'code'   => 'AND',
                    'params' => null,
                ];
            },
            'any'            => function () {
                return [
                    'code'   => '*',
                    'params' => null,
                ];
            },
            'brace'          => function () {
                return [
                    'code'   => '( /@brace(brace) )',
                    'params' => null,
                ];
            },
            'close'          => function () {
                return [
                    'code'   => '/@close',
                    'params' => null,
                ];
            },
            'comma'          => function () {
                return [
                    'code'   => ',',
                    'params' => null,
                ];
            },
            'comparison'     => function (array $params) {
                $allowed = [
                    '<',
                    '>',
                    '<>',
                    '=',
                    '!=',
                    '>=',
                    '<=',
                ];
                if (in_array($params['comparison'], $allowed)) {
                    return [
                        'code'   => $params['comparison'],
                        'params' => null,
                    ];
                }
                throw new MalformedQueryException(
                    'No such comparison known: ' . $params['comparison']
                );
            },
            'delete'         => function (array $params) {
                return [
                    'code'   => 'DELETE `' . $params['table'] . '` FROM `' . $params['table'] . '`',
                    'params' => null,
                ];
            },
            'equals'         => function () {
                return [
                    'code'   => '=',
                    'params' => null,
                ];
            },
            'fieldlist'      => function (array $params) {
                $sql = '';
                $fieldCount = count($params['fields']);
                $idx = 0;
                foreach ($params['fields'] as $key => $value) {
                    if (!is_numeric($key) || substr($value, 0, 1) === '!') {
                        if (substr($value, 0, 1) === '!') {
                            $key = substr($value, 1);
                            $value = $this->toCamelCase($key);
                        }
                        $key = $this->implodeIdentifier($key, self::DELIMITER);
                        $sql .= $key . ' as ' . $this->implodeIdentifier($value, self::DELIMITER);
                    } else {
                        $sql .= $this->implodeIdentifier($value,
                            self::DELIMITER);
                    }
                    if (++$idx < $fieldCount) {
                        $sql .= ', ';
                    }
                }

                return [
                    'code'   => $sql,
                    'params' => null,
                ];
            },
            'field'          => function (array $params) {
                return [
                    'code'   => $this->implodeIdentifier($params['identifier'],
                        self::DELIMITER),
                    'params' => null,
                ];
            },
            'function'       => function (array $params) {
                return [
                    'code'   => strtoupper($params['name']) . '(/@brace(f))',
                    'params' => null,
                ];
            },
            'group'          => function () {
                return [
                    'code'   => 'GROUP BY /@brace(group)',
                    'params' => null,
                ];
            },
            'in'             => function (array $params) {
                if (is_array($params['in'])) {
                    $code = '';
                    $count = count($params['in']);
                    for ($i = 0; $i < $count; $i++) {
                        $code .= '?';
                        if ($i + 1 < $count) {
                            $code .= ', ';
                        }
                    }

                    return [
                        'code'   => 'IN (' . $code . ')',
                        'params' => $params['in'],
                    ];
                }

                return [
                    'code'   => 'IN ( /@brace(in) )',
                    'params' => null,
                ];
            },
            'insert'         => function (array $params) {
                $modes = [
                    'ignore',
                ];
                $mode = null;
                if (in_array($params['mode'], $modes)) {
                    $mode = strtoupper($params['mode']);
                }

                return [
                    'code'   => 'INSERT ' . $mode . ' INTO `' .
                        $params['table'] . '`',
                    'params' => null,
                ];
            },
            'isnull'         => function (array $params) {
                if ($params['isnull']) {
                    return [
                        'code'   => 'IS NULL',
                        'params' => null,
                    ];
                }

                return [
                    'code'   => 'IS NOT NULL',
                    'params' => null,
                ];
            },
            'join'           => function (array $params) {
                $joinTypes = [
                    'left',
                    'right',
                    'inner',
                ];
                if (!in_array($params['type'], $joinTypes)) {
                    throw new MalformedQueryException('Unknown join type');
                }

                $alias = null;
                if (isset($params['alias']) && strlen($params['alias']) > 0) {
                    $alias = ' as `' . $params['alias'] . '`';
                }

                return [
                    'code'   => strtoupper($params['type'])
                        . ' JOIN '
                        . $this->implodeIdentifier($params['table'],
                            self::DELIMITER)
                        . $alias,
                    'params' => null,
                ];
            },
            'like'           => function (array $params) {
                return [
                    'code'   => 'LIKE ?',
                    'params' => $params['pattern'],
                ];
            },
            'limit'          => function (array $params) {
                return [
                    'code'   => 'LIMIT ' . abs((int)$params['limit']),
                    'params' => null,
                ];
            },
            'null'           => function () {
                return [
                    'code'   => 'NULL',
                    'params' => null,
                ];
            },
            'offset'         => function (array $params) {
                return [
                    'code'   => 'OFFSET ' . abs((int)$params['offset']),
                    'params' => null,
                ];
            },
            'on'             => function () {
                return [
                    'code'   => 'ON ( /@brace(on) )',
                    'params' => null,
                ];
            },
            'orderby'        => function (array $params) {
                if ($params['direction'] != 'asc' &&
                    $params['direction'] != 'desc'
                ) {
                    throw new MalformedQueryException('Unknown order type');
                }

                return [
                    'code'   => $this->implodeIdentifier($params['field'],
                            self::DELIMITER)
                        . ' ' . strtoupper($params['direction']),
                    'params' => null,
                ];
            },
            'order'          => function () {
                return [
                    'code'   => 'ORDER BY /@brace(order)',
                    'params' => null,
                ];
            },
            'or'             => function () {
                return [
                    'code'   => 'OR',
                    'params' => null,
                ];
            },
            'sql_found_rows' => function (array $params) {
                $identifier = $params['identifier'];
                if ($params['identifier'] != '*') {
                    $identifier =
                        $this->implodeIdentifier($params['identifier'],
                            self::DELIMITER);
                }

                return [
                    'code'   => 'SQL_CALC_FOUND_ROWS ' . $identifier,
                    'params' => null,
                ];
            },
            'raw'            => function (array $params) {
                return [
                    'code'   => $params['raw'],
                    'params' => $params['params'],
                ];
            },
            'table'          => function (array $params) {
                $table = $params['table'];
                if (is_array($table)) {
                    $table = implode('`.`', $table);
                }
                $alias = null;
                if ($params['alias'] !== null) {
                    $alias = 'as `' . $params['alias'] . '`';
                }

                return [
                    'code'   => 'FROM `' . $table . '` ' . $alias,
                    'params' => null,
                ];
            },
            'union'          => function (array $params) {
                $mode = strtolower($params['mode']);
                if ($mode == 'all') {
                    return 'UNION ALL';
                } else {
                    if ($mode == 'distinct') {
                        return 'UNION DISTINCT';
                    }
                }

                return 'UNION';
            },
            'update'         => function (array $params) {
                return [
                    'code'   => 'UPDATE `' . $params['table'] . '` SET',
                    'params' => null,
                ];
            },
            'using'          => function (array $params) {
                if (is_array($params['field'])) {
                    throw new InvalidOptionException(
                        'Field list for using() clause cannot be array when using MySQL'
                    );
                }

                return [
                    'code'   => 'USING(`' . $params['field'] . '`)',
                    'params' => null,
                ];
            },
            'value'          => function (array $params) {
                return [
                    'code'   => '?',
                    'params' => $params['value'],
                ];
            },
            'values'         => function () {
                return [
                    'code'   => 'VALUES',
                    'params' => null,
                ];
            },
            'where'          => function () {
                return [
                    'code'   => 'WHERE /@brace(where)',
                    'params' => null,
                ];
            },
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            RowMapperEvents::SNIPPET_COLLECTOR => ['onCollectorEvent', 10],
        ];
    }

    public function onCollectorEvent(SnippetBagEvent $event)
    {
        $event->add($this, ['mysql']);
    }

    /**
     * @param string   $name
     * @param \Closure $parser
     * @return void
     * @throws InvalidOptionException if no closure is given
     */
    public function set($name, $parser)
    {
        if ($parser instanceof \Closure) {
            throw new InvalidOptionException(
                'You must give closure as parser for snippet "' . $name . '"'
            );
        }
        $this->snippets[$name] = $parser;
    }

    /**
     * @inheritdoc
     */
    public function get($name)
    {
        if (!isset($this->snippets[$name])) {
            throw new TypeNotFoundException(
                'No snippet named "' . $name . '" known'
            );
        }

        return $this->snippets[$name];
    }

    /**
     * @inheritDoc
     */
    function getAll()
    {
        return $this->snippets;
    }

    /**
     * @inheritDoc
     */
    function has($name)
    {
        return isset($this->snippets[$name]);
    }
}
