<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\Snippets;

use ChrisAndChris\Common\RowMapperBundle\Events\Transmitters\SnippetBagEvent;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\BagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @name SnippetBagInterface
 * @version    1.0.0
 * @since      v2.2.0
 * @lastChange v2.2.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
interface SnippetBagInterface extends BagInterface, EventSubscriberInterface
{

    /**
     * Adds this bag to bag event
     *
     * @param SnippetBagEvent $event
     */
    public function onCollectorEvent(SnippetBagEvent $event);
}
