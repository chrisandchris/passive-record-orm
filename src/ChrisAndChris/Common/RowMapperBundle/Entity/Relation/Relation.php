<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Entity\Relation;

/**
 *
 *
 * @name Relation
 * @version   1.0.0
 * @since     1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class Relation
{

    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $toField;
    /**
     * @var string
     */
    private $fromField;

    /**
     * Relation constructor.
     *
     * @param string      $class
     * @param string      $toField
     * @param string|null $fromField
     * @param string      $mode
     */
    public function __construct(
        string $class,
        string $toField,
        string $fromField = null,
        string $mode = 'early'
    ) {
        $this->class = $class;
        $this->toField = $toField;
        $this->fromField = $fromField;
    }

    /**
     * @return string
     */
    public function getClass() : string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getToField() : string
    {
        return $this->toField;
    }

    /**
     * @return string|null
     */
    public function getFromField()
    {
        return $this->fromField;
    }
}
