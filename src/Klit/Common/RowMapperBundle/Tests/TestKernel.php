<?php
namespace Klit\Common\RowMapperBundle\Tests;
require_once __DIR__.'/../../../../../app/AppKernel.php';

use AppKernel;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @var ContainerInterface
     */
    protected $container;
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
        $this->appKernel = new AppKernel('test', true);
        $this->appKernel->boot();

        $this->container = $this->appKernel->getContainer();
        parent::setUp();
    }

    /**
     * This method is run after each test
     */
    public function tearDown() {
        $this->appKernel->shutdown();
        parent::tearDown();
    }
}
