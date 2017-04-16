<?php

namespace ChrisAndChris\Common\RowMapperBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @name DatabaseMapperCommand
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class DatabaseMapperCommand extends ContainerAwareCommand
{

    /** @var InputInterface */
    private $input;
    /** @var OutputInterface */
    private $output;

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('cac:database:mapper');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $mapper = $this->getContainer()
                       ->get('common_rowmapper.mapping.database_mapper');

        return $mapper->map();
    }
}
