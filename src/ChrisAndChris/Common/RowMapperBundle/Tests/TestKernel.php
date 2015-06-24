<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests;

use AppKernel;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @name TestKernel
 * @version   1
 * @since     v1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
abstract class TestKernel extends WebTestCase {

    /**
     * @var \AppKernel
     */
    protected $appKernel;

    /**
     * @var Client
     */
    protected $client;

    function _construct() {
        $this->client = static::createClient();
    }

    /**
     * This method is run before each test
     */
    public function setUp() {
        parent::setUp();
    }

    /**
     * This method is run after each test
     */
    public function tearDown() {
        parent::tearDown();
    }
}
