<?php

namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\UnknownCastTypeException;

/**
 * Casts arbitrary values to specified values
 *
 * @name TypeCaster
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class TypeCaster
{

    /** @var array */
    public $casts;

    /**
     * TypeCaster constructor.
     */
    public function __construct()
    {
        $this->casts = [
            'int'      => [$this, 'castInt'],
            'json'     => [$this, 'castJson'],
            'bool'     => [$this, 'castBool'],
            'datetime' => [$this, 'castDate'],
        ];
    }

    public function cast($targetType, $value)
    {
        if (!isset($this->casts[$targetType])) {
            throw new UnknownCastTypeException(sprintf(
                'Cannot cast to unknown type %s',
                $targetType
            ));
        }

        return call_user_func($this->casts[$targetType], $value);
    }

    /**
     * @param $value
     * @return int
     */
    public function castInt($value)
    {
        return (int)$value;
    }

    public function castBool($value)
    {
        return (bool)$value;
    }

    public function castJson($value)
    {
        return json_decode($value, true);
    }

    public function castDate($value)
    {
        if ($value instanceof \DateTime) {
            return $value;
        }

        return new \DateTime($value);
    }
}
