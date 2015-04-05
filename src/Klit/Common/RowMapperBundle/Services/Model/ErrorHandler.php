<?php
namespace Klit\Common\RowMapperBundle\Services\Model;

use Klit\Common\RowMapperBundle\Exceptions\DatabaseException;
use Klit\Common\RowMapperBundle\Exceptions\ForeignKeyConstraintException;
use Klit\Common\RowMapperBundle\Exceptions\GeneralDatabaseException;
use Klit\Common\RowMapperBundle\Exceptions\UniqueConstraintException;

/**
 * @name ErrorHandler
 * @version 1.0.0
 * @since v2.0.0
 * @package Common
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class ErrorHandler {
    private $databaseExceptions = array(
        'HY093',    // pdo not enough values bound
        1064,       // syntax error
        1054        // unknown column
    );

    private $uniqueConstraintExceptions = array(
        1062        // unique constraint problem
    );

    private $foreignKeyConstraintExceptions = array(
        1215,
        1216,
        1217,
        1451,
        1452
    );

    /**
     * Validates an MySQL error number
     *
     * @param $errorNum
     * @param $errorText
     * @return bool
     * @throws DatabaseException thrown if this is an error related to the database
     * @throws ForeignKeyConstraintException thrown if this is an error related to a key problem
     * @throws UniqueConstraintException thrown if this is an unique constraint error
     * @throws GeneralDatabaseException thrown otherwise
     */
    public function handle($errorNum, $errorText) {
        if (in_array($errorNum, $this->databaseExceptions)) {
            throw new DatabaseException($errorText);
        }
        if (in_array($errorNum, $this->uniqueConstraintExceptions)) {
            throw new UniqueConstraintException($errorText);
        }
        if (in_array($errorNum, $this->foreignKeyConstraintExceptions)) {
            throw new ForeignKeyConstraintException($errorText);
        }
        throw new GeneralDatabaseException($errorText);
    }
}
