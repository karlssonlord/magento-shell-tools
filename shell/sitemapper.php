<?php
/**
 * Magento Sitemapper Shell Script
 *
 * @category    Mage
 * @package     Mage_Shell
 * @copyright   Copyright (c) 2013 Erik Eng <erik@karlssonlord.com>
 */

require_once 'abstract.php';

/**
 * Magento Sitemapper Shell Script
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Erik Eng <erik@karlssonlord.com>
 */
class Mage_Shell_Sitemapper extends Mage_Shell_Abstract
{

    /**
     * Initialize application and parse input parameters
     *
     */
    public function __construct()
    {
        if ($this->_includeMage) {
            require_once $this->_getRootPath() . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
            Mage::app(Mage::app()->getStore(1)->getCode(), $this->_appType);
        }

        $this->_applyPhpVariables();
        $this->_parseArgs();
        $this->_construct();
        $this->_validate();
        $this->_showHelp();
    }

    /**
     * Copy Template
     *
     * @var string $templatePath
     */
    private function _copyTemplate($templatePath) {
        $source   = Mage::getBaseDir('design').'/'.$templatePath;
        $template = end(explode('template/', $templatePath));
        $dest     = Mage::getDesign()->getTemplateFilename().$template;

        // TODO: Warn if dest already exists

        if(file_exists($source)) {
            echo 'Copying template "'.$template.'" to current package and theme.', "\n";
            echo 'Source:       ', $source, "\n";
            echo 'Destination:  ', $dest, "\n";

            // Create destination dirs if not exists
            $path = '';
            $dirs = array_filter(explode('/', $dest));
            array_pop($dirs);
            foreach($dirs as $dir) {
                $path = $path.'/'.$dir;
                if(!is_dir($path) && !file_exists($path)) {
                    mkdir($path);
                }
            }

            copy($source, $dest);
            if(!file_exists($dest)) {
                echo 'Error:    Could not copy template.', "\n";
            }
        }
        else {
            echo 'Error:  Template path don\'t exists', "\n",
                '   ', $source, "\n";
        }
    }

    /**
     * Validate Template Path
     *
     * @var string $templatePath
     */
    private function _validateTemplatePath($templatePath) {

    }

    /**
     * Run script
     *
     */
    public function run()
    {

        // Load Sitemap Model
        $sitemap = Mage::getModel('sitemap/sitemap');

        if($this->getArg('info')) {
            foreach($sitemap->getCollection()->getData() as $item) {
                $path  = $item['sitemap_path'].$item['sitemap_filename'];
                echo $path, str_repeat(' ', 30-strlen($path)), $item['sitemap_id'], "\n";
            }
        }
        elseif($this->getArg('generate')) {
            $id = end($_SERVER['argv']);
            if($id && $id !== 'generate') {

                $sitemap->load($id);
                // Sitemap record exists
                if($sitemap->getId()) {
                    try {
                        echo 'Generating sitemap "'.$sitemap->getSitemapPath().$sitemap->getSitemapFilename().'"', "\n";
                        $sitemap->generateXml();
                        echo 'Done.', "\n";
                    }
                    catch (Mage_Core_Exception $e) {
                        echo 'Error:  ', $e->getMessage(), "\n";
                        $this->_getSession()->addError($e->getMessage());
                    }
                    catch (Exception $e) {
                        echo 'Error:  Unable to generate the sitemap.', "\n";
                    }
                } else {
                    echo 'Error:  Unable to find a sitemap to generate.', "\n";
                }

            }
            else {
                echo 'Error:  No template path supplied', "\n",
                    'Usage:  php -f templater.php -- copy <path>', "\n";
            }
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f sitemapper.php -- [options]

  info           Show current available sitemaps
  generate <id>  Generate sitemap
  help           This help

  <id>           Sitemap Id

USAGE;
    }
}

$shell = new Mage_Shell_Sitemapper();
$shell->run();