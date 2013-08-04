<?php

namespace Sakura\Command;

use Sakura\Sakura;

use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;

use Seld\JsonLint\JsonParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

use Herrera\Json\Json;

/**
 * Class SelfUpdateCommand
 *
 * @package Sakura\Command
 */
class SelfUpdateCommand extends Command {
    const MANIFEST_FILE = "http://alberteddu.github.com/sakura/manifest.json";

    public function configure() {
        $this->setName('self-update')
            ->setDescription('Update ' . Sakura::APPLICATION_NAME . ' to the last version');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $manager = new Manager(Manifest::loadFile(self::MANIFEST_FILE));
        $manager->update($this->getApplication()->getVersion(), true);
    }
}