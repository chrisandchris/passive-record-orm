<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\DatabaseException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\ForeignKeyConstraintException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\GeneralDatabaseException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\UniqueConstraintException;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ErrorHandler;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name ErrorHandlerTest
 * @version 1.0.0
 * @since v2.0.0
 * @package Common
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class ErrorHandlerTest extends TestKernel {
    public function testHandle() {
        $Handler = new ErrorHandler();

        $this->handleDatabaseException(1064);
        $this->handleDatabaseException(1054);
        $this->handleDatabaseException('HY093');

        $this->handleUniqueConstraintException(1062);

        $this->handleForeignKeyConstraintException(1215);
        $this->handleForeignKeyConstraintException(1216);
        $this->handleForeignKeyConstraintException(1217);
        $this->handleForeignKeyConstraintException(1451);
        $this->handleForeignKeyConstraintException(1452);

        $this->handleGeneralDatabaseException(-1);
        $this->handleGeneralDatabaseException(false);
        $this->handleGeneralDatabaseException(1);
        $this->handleGeneralDatabaseException(null);
    }

    private function handleDatabaseException($num) {
        try {
            (new ErrorHandler())->handle($num, null);
            $this->fail('Must fail with exception');
        } catch (DatabaseException $E) {
            // ignore
        } catch (\Exception $E) {
            $this->fail('Must fail with database exception');
        }
    }

    private function handleUniqueConstraintException($num) {
        try {
            (new ErrorHandler())->handle($num, null);
            $this->fail('Must fail with exception');
        } catch (UniqueConstraintException $E) {
            // ignore
        } catch (\Exception $E) {
            $this->fail('Must fail with unique constraint exception');
        }
    }

    private function handleForeignKeyConstraintException($num) {
        try {
            (new ErrorHandler())->handle($num, null);
            $this->fail('Must fail with exception');
        } catch (ForeignKeyConstraintException $E) {
            // ignore
        } catch (\Exception $E) {
            $this->fail('Must fail with foreign key constraint exception');
        }
    }

    private function handleGeneralDatabaseException($num) {
        try {
            (new ErrorHandler())->handle($num, null);
            $this->fail('Must fail with exception');
        } catch (GeneralDatabaseException $E) {
            // ignore
        } catch (\Exception $E) {
            $this->fail('Must fail with general database exception');
        }
    }
}
