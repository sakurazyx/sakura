<?php

namespace Sakura\Command;

use Sakura\Sakura;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SelfUpdateCommand
 *
 * @package Sakura\Command
 */
class SelfUpdateCommand extends Command {
    public function configure() {
        $this->setName('self-update')
            ->setDescription('Update ' . Sakura::APPLICATION_NAME . ' to the last version');
    }

    public function execute(InputInterface $input, OutputInterface $output) {

    }
}