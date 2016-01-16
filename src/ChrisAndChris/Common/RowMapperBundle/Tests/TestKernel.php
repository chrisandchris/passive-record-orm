<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests;

require_once __DIR__ . '/../../../../../app/AppKernel.php';

use AppKernel;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Kernel used for any unit test, extend always for any test you write
 *
 * @name TestKernel
 * @version   3
 * @since     v1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
abstract class TestKernel extends TestCase
{

    /**
     * @var \AppKernel
     */
    protected $appKernel;
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * This method is run before each test<br>
     * Please avoid overriding this class, it provides the schema initialization and creates the
     * AppKernel and the client
     */
    public function setUp()
    {
        $this->appKernel = new AppKernel('test', true);
        $this->appKernel->boot();

        $this->container = $this->appKernel->getContainer();

        parent::setUp();
        $this->setUpClass();
        $this->beforeEachTest();
    }

    /**
     * Set up the test kernel class<br>
     * Do not override, please use beforeEachTest()
     */
    protected function setUpClass()
    {
        if (ini_get('max_execution_time') != -1) {
            ini_set('max_execution_time', '-1');
        }
        ini_set('display_errors', 'stderr');
        error_reporting(E_ALL);
    }

    /**
     * Override this method to provide functionality before each test
     */
    protected function beforeEachTest()
    {
        // do nothing
    }

    /**
     * This method is run after each test
     */
    public function tearDown()
    {
        if (is_object($this->appKernel)) {
            $this->appKernel->shutdown();
        }
        parent::tearDown();
    }

    /**
     * Compare given properties of two objects
     *
     * @param object $A
     * @param object $B
     * @param array  $properties
     */
    protected function compareProperties($A, $B, array $properties)
    {
        if (!is_object($A) || !is_object($B)) {
            $this->fail('You must give me two objects');
        }
        $ClassA = new \ReflectionClass($A);
        $ClassB = new \ReflectionClass($B);

        if (!($A instanceof $B)) {
            $this->fail('A and B must have same type');
        }
        foreach ($properties as $property) {
            try {
                $PropertyA = $ClassA->getProperty($property);
                $PropertyA->setAccessible(true);
                $PropertyB = $ClassB->getProperty($property);
                $PropertyB->setAccessible(true);

                if (is_scalar($PropertyA->getValue($A)) && is_scalar($PropertyB->getValue($B))) {
                    $message = "Property '" . $property . "' not equal ('" . $PropertyA->getValue($A) . "' vs '"
                        . $PropertyB->getValue($B) . "')";
                } else {
                    $message = null;
                }
                $this->assertEquals(
                    $PropertyA->getValue($A),
                    $PropertyB->getValue($B),
                    $message);
            } catch (\ReflectionException $E) {
                $this->fail($E->getMessage());
            }
        }
    }
}
