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
        $this->connections['w'] = [$layer];
        $this->connections['r'] = [];

        foreach ($params as $type => $pool) {
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
     * @return \PDO
     */
    public function getPdo(string $type = null) : \PDO
    {
        if ($type === null || count($this->connections['type']) == 0) {
            return $this->getRandom($this->connections['w']);
        }

        return $this->getRandom($this->connections[$type]);
    }

    /**
     * Get a random connection from list of connections
     *
     * @param array $connections
     * @return \PDO
     */
    private function getRandom(array $connections) : \PDO
    {
        return array_rand($connections);
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
