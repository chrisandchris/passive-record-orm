<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests;

use AppKernel;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @name TestKernel
 * @version   2
 * @since     v1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
abstract class TestKernel extends WebTestCase {

    /** @var \AppKernel */
    protected $appKernel;
    /** @var Client */
    protected $client;
    /** @var ContainerInterface */
    protected $container;

    /**
     * This method is run before each test
     */
    public function setUp() {
        $this->client = static::createClient();

        $this->appKernel = new AppKernel('test', true);
        $this->appKernel->boot();

        $this->container = $this->appKernel->getContainer();
    }

    /**
     * This method is run after each test
     */
    public function tearDown() {
        parent::tearDown();
    }
}
