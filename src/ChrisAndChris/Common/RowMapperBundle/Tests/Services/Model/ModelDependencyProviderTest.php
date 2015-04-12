<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Services\Logger\LoggerInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Logger\PdoLogger;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ErrorHandler;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ModelDependencyProvider;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\RowMapper;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name ModelDependencyProviderTest
 * @version 1.0.0
 * @since v2.0.0
 * @package Common
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class ModelDependencyProviderTest extends TestKernel {

    public function testConstructor() {
        $DP = new ModelDependencyProvider(
            new PdoLayer('sqlite', 'sqlite.db'),
            new RowMapper(),
            new ErrorHandler(),
            new PdoLogger('sqlite', 'log.db'),
            new Builder(new DefaultParser())
        );

        $this->assertTrue($DP->getBuilder() instanceof Builder);
        $this->assertTrue($DP->getPDO() instanceof PdoLayer);
        $this->assertTrue($DP->getErrorHandler() instanceof ErrorHandler);
        $this->assertTrue($DP->getMapper() instanceof RowMapper);
        $this->assertTrue($DP->getLogger() instanceof LoggerInterface);
    }
}
