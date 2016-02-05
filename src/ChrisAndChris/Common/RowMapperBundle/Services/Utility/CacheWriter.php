<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Utility;

/**
 * @name CacheWriter
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class CacheWriter {

    /**
     * @param $cacheDir
     * @param $dir
     */
    public function __construct($cacheDir, $dir) {
        $this->cacheDir = $cacheDir . '/' . $dir . '/';
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0766, true);
        }
    }

    /**
     * @param $file
     * @param $content
     * @return int
     */
    public function writeToCache($file, $content) {
        return file_put_contents($this->cacheDir . basename($file), $content);
    }

    /**
     * @param $file
     * @return null|string
     */
    public function readFromCache($file) {
        $file = basename($file);
        if (is_file($this->cacheDir . $file)) {
            return file_get_contents($this->cacheDir . $file);
        }

        return null;
    }
}
