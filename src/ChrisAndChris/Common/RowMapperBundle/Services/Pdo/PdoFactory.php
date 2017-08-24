<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Services\Pdo;

/**
 * The PdoFactory returns connections from a pool based on read or write
 * preference
 *
 * @name PdoFactory
 * @version   1.1.1
 * @since     v2.4
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class PdoFactory
{

    /**
     * @var array
     */
    private $params;
    /** @var [PdoLayer[]] */
    private $connections = [];

    public function __construct(PdoLayer $layer, array $params = [])
    {
        // default connection defaults to write
        $this->connections['w'] = [$layer];
        $this->connections['r'] = [];

        foreach ($params as $type => $pool) {
            if (!is_array($pool)) {
                continue;
            }
            if ($type !== 'read' && $type !== 'write') {
                continue;
            }
            $type = substr($type, 0, 1);
            foreach ($pool as $connection) {
                $this->connections[$type][] = new PdoLayer(
                    $connection[0], // system
                    $connection[1], // host
                    $connection[2], // port
                    $connection[3], // database
                    $connection[4], // user
                    $connection[5] // password
                );
            }
        }
    }

    /**
     * Get a read or a write connection
     *
     * @param string|null $type either null, r or w
     * @return \ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer
     */
    public function getPdo(string $type = null) : PdoLayer
    {
        if ($type === null || count($this->connections[$type]) == 0) {
            $conn = $this->getRandom($this->connections['w']);
        } else {
            return $this->getRandom($this->connections[$type]);
        }

        if ($conn instanceof \Closure) {
            return $conn();
        }

        return $conn;
    }

    /**
     * Get a random connection from list of connections
     *
     * @param array $connections
     * @return \ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer
     */
    private function getRandom(array $connections) : PdoLayer
    {
        return $connections[array_rand($connections)];
    }

    public function getReadPoolCount() : int
    {
        return count($this->connections['r']);
    }

    public function getWritePoolCount() : int
    {
        return count($this->connections['w']);
    }
}
