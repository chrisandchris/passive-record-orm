<?php
namespace Klit\Common\RowMapperBundle\Services\Model;

use Klit\Common\RowMapperBundle\Exceptions\DatabaseException;
use Klit\Common\RowMapperBundle\Exceptions\ForeignKeyConstraintException;
use Klit\Common\RowMapperBundle\Exceptions\UniqueConstraintException;

/**
 * @name ErrorHandler
 * @version 1.0.0
 * @package CommonRowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class ErrorHandler {
    private $databaseExceptions = array(
        'HY093',
        1064
    );

    private $uniqueConstraintExceptions = array(
        1062
    );

    private $foreignKeyConstraintExceptions = array(
        1215,
        1216,
        1217,
        1451,
        1452
    );

    /**
     * Validates an error number
     *
     * @param $errorNum
     * @param $errorText
     * @return bool
     * @throws DatabaseException
     * @throws ForeignKeyConstraintException
     * @throws UniqueConstraintException
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
        return false;
    }
}
