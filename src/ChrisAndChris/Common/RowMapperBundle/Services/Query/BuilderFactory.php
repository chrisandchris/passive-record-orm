<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\ParserInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\TypeBag;

/**
 * @name BuilderFactory
 * @version   1.0.0
 * @since     v2.1.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class BuilderFactory {

    /** @var ParserInterface */
    private $parser;
    /** @var TypeBag */
    private $typeBag;

    /**
     * BuilderFactory constructor.
     *
     * @param ParserInterface $parser
     * @param TypeBag         $typeBag
     */
    public function __construct(ParserInterface $parser, TypeBag $typeBag) {
        $this->parser = $parser;
        $this->typeBag = $typeBag;
    }

    /**
     * @return Builder
     */
    public function createBuilder() {
        return new Builder($this->parser, $this->typeBag);
    }
}
