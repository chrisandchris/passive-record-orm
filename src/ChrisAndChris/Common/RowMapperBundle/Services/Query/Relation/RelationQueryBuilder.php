<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Relation;

use ChrisAndChris\Common\RowMapperBundle\Entity\Relation\RelatedEntity;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;

/**
 *
 *
 * @name RelationQueryBuilder
 * @version   1.0.0
 * @since     1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class RelationQueryBuilder
{

    public function build(
        Builder $builder,
        string $entity
    ) : Builder {
        /** @var \ChrisAndChris\Common\RowMapperBundle\Entity\Relation\RelatedEntity $instance */
        $instance = new $entity;

        $this->columns($builder, $instance);

        /** @var \ChrisAndChris\Common\RowMapperBundle\Entity\Relation\Relation[] $relations */
        $relations = [];
        if (count($instance->getRelations()) > 0) {
            foreach ($instance->getRelations() as $relation) {
                $relations[] = $relation;
                $relatedInstance = $relation->getClass();
                $relatedInstance = new $relatedInstance;
                $this->columns($builder, $relatedInstance, true);
            }
        }

        $builder->table($instance->getLogicalName());

        foreach ($relations as $relation) {
            $className = $relation->getClass();
            /** @var RelatedEntity $relatedInstance */
            $relatedInstance = new $className;
            $builder->join($relatedInstance->getLogicalName());
            if ($relation->getFromField() !== null) {
                // @formatter:off
                $builder->on()
                    ->field(sprintf('%s:%s', $relatedInstance->getLogicalName(), $relation->getFromField()))
                    ->equals()
                    ->field(sprintf('%s:%s', $instance->getLogicalName(), $relation->getToField()))
                ->close();
                // @formatter:on
                continue;
            }
            $builder->using($relation->getToField());
        }

        return $builder;
    }

    public function columns(
        Builder $builder,
        RelatedEntity $instance,
        bool $qualifiedNames = false
    ) {
        $keys = array_keys(get_object_vars($instance));
        foreach ($keys as $index => $property) {
            if ($qualifiedNames) {
                $builder->field(sprintf(
                    '%s:%s',
                    $instance->getLogicalName(),
                    $this->fromCamelCaseToDash($property)
                ));
            } else {
                $builder->field($this->fromCamelCaseToDash($property));
            }
            if ($index + 1 < count($keys)) {
                $builder->c();
            }
        }
    }

    public function fromCamelCaseToDash(string $string) : string
    {
        return preg_replace_callback('/([A-Z])/', function ($string) {
            return '_' . strtolower($string[0]);
        }, $string);
    }
}
