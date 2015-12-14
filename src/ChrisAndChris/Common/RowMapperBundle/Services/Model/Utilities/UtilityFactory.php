<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Utilities;

use ChrisAndChris\Common\RowMapperBundle\Services\Model\ModelDependencyProvider;

/**
 * @name UtilityFactory
 * @version
 * @since     v2.1.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class UtilityFactory {

    /** @var ModelDependencyProvider */
    private $dependencyProvider;


    /**
     * UtilityFactory constructor.
     */
    public function __construct(ModelDependencyProvider $dependencyProvider) {
        $this->dependencyProvider = $dependencyProvider;
    }

    public function getSearchable() {
        return new SearchQueryBuilder($this->dependencyProvier);
    }
}
