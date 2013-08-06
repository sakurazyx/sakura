<?php

namespace Sakura\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class VersionAvailableCommand extends Command {
    /** @var string Version */
    private $version;

    /**
     * $version is the version available for download
     *
     * @param null|string $version
     */
    public function __construct($version) {
        $this->version = $version;

        parent::__construct();
    }

    public function configure() {
        $this->setName('New version');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $style = new OutputFormatterStyle('red', null, array('bold'));
        $output->getFormatter()->setStyle('version-available', $style);

        $version = $this->version;
        
        $output->writeln("<version-available>There is a new version available ($version). To update, use the command self-update.</version-available>\n");
    }
}