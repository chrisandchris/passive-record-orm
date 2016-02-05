<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests;

/**
 * @name TestKernel
 * @version   4
 * @since     v1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
abstract class TestKernel extends \PHPUnit_Framework_TestCase
{

    /**
     * Compare given properties of two objects
     *
     * @param object $left
     * @param object $right
     * @param array  $properties
     */
    protected function compareProperties($left, $right, array $properties)
    {
        if (!is_object($left) || !is_object($right)) {
            $this->fail('You must give me two objects');
        }
        $leftClass = new \ReflectionClass($left);
        $rightClass = new \ReflectionClass($right);

        if (!($left instanceof $right)) {
            $this->fail('A and B must have same type');
        }
        foreach ($properties as $property) {
            try {
                $leftProperty = $leftClass->getProperty($property);
                $leftProperty->setAccessible(true);
                $rightProperty = $rightClass->getProperty($property);
                $rightProperty->setAccessible(true);

                if (is_scalar($leftProperty->getValue($left)) && is_scalar($rightProperty->getValue($right))) {
                    $message = sprintf('Property "%s" not equal ("%s" vs "%s")',
                        $property,
                        $leftProperty->getValue($left),
                        $rightProperty->getValue($right)
                    );
                } else {
                    $message = null;
                }
                $this->assertEquals(
                    $leftProperty->getValue($left),
                    $rightProperty->getValue($right),
                    $message);
            } catch (\ReflectionException $exception) {
                $this->fail($exception->getMessage());
            }
        }
    }
}
