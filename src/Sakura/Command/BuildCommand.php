<?php

namespace Sakura\Command;

use Sakura\Sakura;
use Sakura\Exception\DirectoryNotFoundException;
use Sakura\Exception\ConfigFileNotFoundException;
use Sakura\Exception\ContentDirectoryNotFoundException;
use Sakura\Exception\SiteDirectoryNotFoundException;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BuildCommand
 *
 * @package Sakura\Command
 */
class BuildCommand extends Command {
    /** @var \Sakura\Sakura Instance of Sakura */
    public $sakura;

    /**
     * Configure build command
     */
    public function configure() {
        $this->setName('build')
            ->setDescription('Build website')
            ->addArgument('directory', InputArgument::OPTIONAL, 'Directory that contains the file ' . Sakura::CONFIG_FILE, '.')
            ->addOption('content-directory', 'c', InputOption::VALUE_OPTIONAL, 'Content directory name', Sakura::DEFAULT_CONTENT_DIRECTORY_NAME)
            ->addOption('templates-directory', 't', InputOption::VALUE_OPTIONAL, 'Templates directory name', Sakura::DEFAULT_TEMPLATES_DIRECTORY_NAME)
            ->addOption('site-directory', 's', InputOption::VALUE_OPTIONAL, 'Site directory name', Sakura::DEFAULT_SITE_DIRECTORY_NAME);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        $directory          = $input->getArgument('directory');
        $contentDirectory   = $input->getOption('content-directory');
        $siteDirectory      = $input->getOption('site-directory');
        $templatesDirectory = $input->getOption('templates-directory');

        $this->sakura = new Sakura($directory, $contentDirectory, $siteDirectory, $templatesDirectory);

        // We are all set.
        $this->sakura->build();
    }
}