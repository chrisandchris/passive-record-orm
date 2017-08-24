<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapper\ChrisAndChris\Common\RowMapperBundle\Services\Pdo;

use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer;

/**
 * @name PdoLayerPromise
 * @version    1.0.0
 * @since
 * @package    JayCool
 * @subpackage
 * @author     Christian Klauenbösch <cklauenboesch@globalelements.ch>
 * @copyright  Global Elements GmbH
 * @link       https://www.globalelements.ch
 */
class PdoLayerPromise
{

    function getPromise(
        $system,
        $host,
        $port = null,
        $name = null,
        $user = null,
        $password = null
    ) {
        return function () use (
            $system,
            $host,
            $port,
            $name,
            $user,
            $password
        ) {
            return new PdoLayer(
                $system,
                $host,
                $port,
                $name,
                $user,
                $password
            );
        };
    }
}
