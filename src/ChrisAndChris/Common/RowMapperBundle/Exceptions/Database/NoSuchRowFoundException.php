<?php

namespace ChrisAndChris\Common\RowMapperBundle\Exceptions\Database;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Indicates that there was no matching row found
 *
 * LEGACY: Does extend NotFoundHttpException - to be removed/changed in >=v2.2
 *
 * @name NoSuchRowFoundException
 * @version   1.0.0
 * @since     1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class NoSuchRowFoundException extends NotFoundHttpException
{

}
