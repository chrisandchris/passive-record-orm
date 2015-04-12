<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests;

use AppKernel;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @name TestKernel
 * @version 1.0.0
 * @package CommonRowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
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
