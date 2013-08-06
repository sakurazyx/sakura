<?php

namespace Sakura;

use Sakura\Branches\Branches;
use Sakura\Branches\Page\Page;
use Symfony\Component\Yaml\Yaml;
use Sakura\Exception\DirectoryNotFoundException;
use Sakura\Exception\ConfigFileNotFoundException;
use Sakura\Exception\ContentDirectoryNotFoundException;
use Sakura\Exception\SiteDirectoryInvalidException;
use Sakura\Exception\TemplatesDirectoryNotFoundException;

/**
 * Class Sakura
 *
 * @package Sakura
 */
class Sakura {
    /** Application name */
    const APPLICATION_NAME = 'Sakura';

    /** Application version */
    const APPLICATION_VERSION = '0.0.1';

    /** Config file */
    const CONFIG_FILE = '_sakura.yml';

    /** Default content directory name */
    const DEFAULT_CONTENT_DIRECTORY_NAME = '_content';

    /** Default templates directory name */
    const DEFAULT_TEMPLATES_DIRECTORY_NAME = '_templates';

    /** Default site directory name */
    const DEFAULT_SITE_DIRECTORY_NAME = '_site';

    /** @var string Directory path */
    protected $directory;

    /** @var string Content directory name */
    protected $contentDirectory;

    /** @var string Templates directory name */
    protected $templatesDirectory;

    /** @var string Site directory name */
    protected $siteDirectory;

    /** @var string Branches instance */
    protected $branches;

    /** @var Site Instance of site to be passed to templates */
    protected $site;

    /** @var \Twig_Environment Instance of twig */
    protected $twig;

    /**
     * @param        $directory
     * @param string $contentDirectory
     * @param string $siteDirectory
     * @param string $templatesDirectory
     *
     * @throws ContentDirectoryNotFoundException
     * @throws ConfigFileNotFoundException
     * @throws SiteDirectoryInvalidException
     * @throws DirectoryNotFoundException
     * @throws TemplatesDirectoryNotFoundException
     */
    public function __construct($directory, $contentDirectory = null, $siteDirectory = null, $templatesDirectory = null) {
        $directory = static::normalizeDirectoryPath($directory);

        if(!static::validDirectory($directory)) throw new DirectoryNotFoundException($directory);

        $this->directory = realpath($directory);
        $siteProperties  = $this->siteProperties();

        $this->contentDirectory   = $contentDirectory != self::DEFAULT_CONTENT_DIRECTORY_NAME ? $contentDirectory : ((isset($siteProperties['_build']) && isset($siteProperties['_build']['content-directory'])) ? $siteProperties['_build']['content-directory'] : self::DEFAULT_CONTENT_DIRECTORY_NAME);
        $this->templatesDirectory = $templatesDirectory != self::DEFAULT_TEMPLATES_DIRECTORY_NAME ? $templatesDirectory : ((isset($siteProperties['_build']) && isset($siteProperties['_build']['templates-directory'])) ? $siteProperties['_build']['templates-directory'] : self::DEFAULT_TEMPLATES_DIRECTORY_NAME);
        $this->siteDirectory      = $siteDirectory != self::DEFAULT_SITE_DIRECTORY_NAME ? $siteDirectory : ((isset($siteProperties['_build']) && isset($siteProperties['_build']['site-directory'])) ? $siteProperties['_build']['site-directory'] : self::DEFAULT_SITE_DIRECTORY_NAME);

        if(!static::validConfigFile($directory)) throw new ConfigFileNotFoundException($directory, self::CONFIG_FILE);
        if(!static::validContentDirectory($directory, $this->contentDirectory)) throw new ContentDirectoryNotFoundException($directory, $this->contentDirectory);
        if(!static::validTemplatesDirectory($directory, $this->templatesDirectory)) throw new TemplatesDirectoryNotFoundException($directory, $this->templatesDirectory);
        if(!static::validSiteDirectory($directory)) throw new SiteDirectoryInvalidException($directory, $this->siteDirectory);

        // Set up branches
        $this->branches = new Branches($directory . '/' . $contentDirectory);
        $this->branches->setNodeProvider(array($this, 'nodeProvider'));
        $this->branches->registerPropertiesCallback(array($this, 'addInvisibleToPage'));

        // Set up site
        $this->site = new Site($siteProperties);

        // Set up twig
        $loader     = new \Twig_Loader_Filesystem($this->pathAt($this->templatesDirectory));
        $this->twig = new \Twig_Environment($loader, array(
            'debug' => true
        ));
        $this->twig->addExtension(new \Twig_Extension_Debug);
    }

    public function getContentDirectory() {
        return $this->contentDirectory;
    }

    public function getBranches() {
        return $this->branches;
    }

    /**
     * Add invisible property
     * if directory name starts
     * with an underscore.
     *
     * @param Page $page
     *
     * @return array
     */
    public function addInvisibleToPage(Page $page) {
        return array(
            'visible' => substr(basename($page->path()), 0, 1) != '_'
        );
    }

    /**
     * Branches node provider
     *
     * @param          $url
     * @param          $path
     * @param Branches $branches
     *
     * @return Page
     */
    public function nodeProvider($url, $path, Branches $branches) {
        return new Page($url, $path, $branches);
    }

    /**
     * Parses the configuration file
     * and returns an array of properties
     *
     * @return array
     */
    public function siteProperties() {
        return Yaml::parse(file_get_contents($this->pathAt(self::CONFIG_FILE)));
    }

    /**
     * Build website
     */
    public function build() {
        if(is_dir($this->pathAt($this->siteDirectory))) {
            static::removeDirectory($this->pathAt($this->siteDirectory));
        }

        mkdir($this->pathAt($this->siteDirectory));

        $this->moveFilesFromTo($this->directory, $this->pathAt($this->siteDirectory));

        $this->buildAtUrl();
    }

    /**
     * Build post at $url
     * and recursively build its children
     *
     * @param string $url
     */
    protected function buildAtUrl($url = '') {
        $page = $this->branches->get($url);
        $path = $this->pathAt($this->siteDirectory . $page->url(true));

        if(!is_dir($path)) mkdir($path);

        $content = $this->renderPageAtUrl($page);

        file_put_contents($path . '/index.html', $content);

        foreach($page->children()->wherePropertyIs('visible', true) as $child) {
            $this->buildAtUrl($child->url());
        }
    }

    /**
     * @param string|Page $page
     *
     * @return string
     */
    public function renderPageAtUrl($page) {
        if(is_string($page)) {
            $page = $this->branches->get($page);
        }

        return $this->twig->render($this->templateNameForPage($page) . '.twig', array(
            'site' => $this->site,
            'page' => $page
        ));
    }

    public function moveFilesFromTo($from, $to) {
        foreach(new \RecursiveDirectoryIterator($from, \RecursiveDirectoryIterator::SKIP_DOTS) as $item) {
            if(substr($item->getFilename(), 0, 1) == '_') continue;

            if($item->isDir()) {
                mkdir($to . DIRECTORY_SEPARATOR . $item->getBasename());
                $this->moveFilesFromTo($from . DIRECTORY_SEPARATOR . $item->getBasename(), $to . DIRECTORY_SEPARATOR . $item->getBasename());
            } else {
                copy($item, $to . DIRECTORY_SEPARATOR . $item->getBasename());
            }
        }
    }

    /**
     * @param Page $page
     *
     * @return string
     */
    protected function templateNameForPage(Page $page) {
        return $page->layout ? $page->layout : 'page';
    }

    /**
     * Returns path relative
     * to website directory
     *
     * @param $path
     *
     * @return string
     */
    public function pathAt($path) {
        return $this->directory . '/' . static::normalizeDirectoryPath($path);
    }

    /**
     * Checks if directory is readable.
     *
     * @param string $directory
     *
     * @return bool
     */
    public static function validDirectory($directory) {
        $directory = static::normalizeDirectoryPath($directory);

        return is_dir($directory) and is_readable($directory);
    }

    /**
     * Checks if directory contains readable
     * sakura config file.
     *
     * @param string $directory
     *
     * @return bool
     */
    public static function validConfigFile($directory) {
        $directory = static::normalizeDirectoryPath($directory);
        $file      = $directory . '/' . self::CONFIG_FILE;

        return is_file($file) and is_readable($file);
    }

    /**
     * Checks if directory contains readable
     * content directory.
     *
     * @param string $directory
     * @param string $name
     *
     * @return bool
     */
    public static function validContentDirectory($directory, $name) {
        $directory = static::normalizeDirectoryPath($directory);
        $file      = $directory . '/' . $name;

        return is_dir($file) and is_readable($file);
    }

    /**
     * Checks if directory contains readable
     * and writable templates directory.
     *
     * @param $directory
     * @param $name
     *
     * @return bool
     */
    public static function validTemplatesDirectory($directory, $name) {
        $directory = static::normalizeDirectoryPath($directory);
        $file      = $directory . '/' . $name;

        return is_dir($file) and is_readable($file);
    }

    /**
     * Checks if directory contains readable
     * and writable site directory.
     *
     * @param $directory
     *
     * @return bool
     */
    public static function validSiteDirectory($directory) {
        $directory = static::normalizeDirectoryPath($directory);

        return is_dir($directory) and is_writable($directory);
    }

    /**
     * Always return path without trailing slash
     *
     * @param string $directory
     *
     * @return string
     */
    public static function normalizeDirectoryPath($directory) {
        if(substr($directory, -1, 1) == '/') {
            $directory = substr($directory, 0, -1);
        }

        return $directory;
    }

    /**
     * @param $directory
     */
    public static function removeDirectory($directory) {
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
        }

        rmdir($directory);
    }
}