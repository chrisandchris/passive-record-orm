<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\DatabaseException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\ForeignKeyConstraintException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\GeneralDatabaseException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\UniqueConstraintException;

/**
 * @name ErrorHandler
 * @version    1.0.0
 * @since      v2.0.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class ErrorHandler
{

    private $databaseExceptions = [
        'HY000',    // general error
        'HY093',    // pdo not enough values bound
        1064,       // syntax error
        1054        // unknown column
    ];

    private $uniqueConstraintExceptions = [
        1062        // unique constraint problem
    ];

    private $foreignKeyConstraintExceptions = [
        1215,
        1216,
        1217,
        1451,
        1452,
    ];

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
    public function handle($errorNum, $errorText)
    {
        if (in_array($errorNum, $this->databaseExceptions)) {
            throw new DatabaseException($this->getErrorText($errorText, $errorNum));
        }
        if (in_array($errorNum, $this->uniqueConstraintExceptions)) {
            throw new UniqueConstraintException($this->getErrorText($errorText, $errorNum));
        }
        if (in_array($errorNum, $this->foreignKeyConstraintExceptions)) {
            throw new ForeignKeyConstraintException($this->getErrorText($errorText, $errorNum));
        }
        throw new GeneralDatabaseException($this->getErrorText($errorText, $errorNum));
    }

    private function getErrorText($errorText, $errorNum)
    {
        return sprintf(
            'Error "%s" with code "%s"',
            $errorText,
            $errorNum
        );
    }
}
