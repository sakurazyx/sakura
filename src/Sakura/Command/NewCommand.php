<?php

namespace Sakura\Command;

use Sakura\Sakura;
use Sakura\Branches\Branches;

use \Soar\Soar;
use \Soar\Directory;
use \Soar\File;
use \Soar\Exception\DirectoryAlreadyExistsException;

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
class NewCommand extends Command {
    /**
     * Configure new command
     */
    public function configure() {
        $this->setName('new')
            ->setDescription('Generate new website')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of directory to generate')
            ->addOption('in-directory', 'd', InputOption::VALUE_OPTIONAL, 'Where to generate files', '.')
            ->addOption('title', 't', InputOption::VALUE_OPTIONAL, 'Title of the new website', '')
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'Overwrite existing directory');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        $path      = $input->getOption('in-directory');
        $name      = $input->getArgument('name');
        $title     = $input->getOption('title');
        $overwrite = $input->getOption('overwrite');
        $path      = Soar::normalizeDirectory($path) . '/' . Soar::normalizeDirectory($name);
        $name      = basename($path);
        $path      = dirname($path);

        if($title == '') {
            $title = $name;
        }

        $options = array(
            'name'  => $name,
            'title' => $title
        );

        try {
            $soar = new Soar($path, $this->template(), $options);
        } catch(DirectoryAlreadyExistsException $e) {
            if(!$overwrite) {
                throw new \Exception('Directory ' . $name . ' already exists. To overwrite, run with --overwrite.');
            }

            Sakura::removeDirectory(Soar::normalizeDirectory($path) . '/' . $name);

            $soar = new Soar($path, $this->template(), $options);
        }

        $soar->build();
    }

    /**
     * @return Directory
     */
    private function template() {
        return new Directory(
            '{{ name }}',
            array(
                new File(Sakura::CONFIG_FILE, $this->configFileContents()),
                new Directory(Sakura::DEFAULT_CONTENT_DIRECTORY_NAME),
                new Directory(Sakura::DEFAULT_TEMPLATES_DIRECTORY_NAME)
            )
        );
    }

    /**
     * @return string
     */
    private function configFileContents() {
        $contents = <<<END
title: {{ title }}
END;

        return $contents;
    }
}