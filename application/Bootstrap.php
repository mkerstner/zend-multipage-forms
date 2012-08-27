<?php

/**
 * @author matthias.kerstner <matthias@kerstner.at> 
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    /**
     * Init Autoloader
     */
    public function _initAutoloader() {
        // add namespaces for libraries
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('Custom_');

        //autoloading of modules is controlled via application.ini:
        //resources.modules[] = ""
        //+ be sure to include a Boostrap.php in every modules to autoload it!

        $autoloader->suppressNotFoundWarnings(false);

        return $autoloader;
    }

    /**
     * Init Zend_Registry
     */
    protected function _initRegistry() {
        Zend_Registry::setInstance(new Zend_Registry());
        $this->_registry = Zend_Registry::getInstance();
    }

    /**
     * 
     */
    public function _initConfig() {
        $config = new Zend_Config_Ini(APPLICATION_PATH .
                        DIRECTORY_SEPARATOR . 'configs' .
                        DIRECTORY_SEPARATOR . 'application.ini', APPLICATION_ENV);

        Zend_Registry::set('Zend_Config', $config);

        $this->_registry->config = new stdClass();
        $this->_registry->config->application = $config;
    }

    /**
     * Setup internal encoding -> be sure to use UTF-8. 
     */
    public function _initEncoding() {
        $config = Zend_Registry::get('Zend_Config');
        mb_internal_encoding($config->phpSettings->mb_internal_encoding);
        mb_regex_encoding($config->phpSettings->mb_regex_encoding);
    }

    /**
     * Auto-load modules.
     */
    protected function _initAppAutoload() {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
                    'namespace' => '',
                    'basePath' => dirname(__FILE__)));
    }

    public function _initErrorhandler() {
        $plugin = new Zend_Controller_Plugin_ErrorHandler();
        $plugin->setErrorHandler(array(
            'module' => 'error',
            'controller' => 'index',
            'action' => 'index'
        ));
        Zend_Controller_Front::getInstance()->registerPlugin($plugin);
    }

    /**
     * Init view and helper
     */
    public function _initView() {
        // make custom view helpers available
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->initView();

        $viewRenderer->view->addHelperPath(APPLICATION_PATH . '/views/helpers/', 'Custom_View_Helper_');

        // add default view scripts to path
        $viewRenderer->view->addScriptPath(APPLICATION_PATH . '/views/scripts/');

        // init doctype
        $viewRenderer->view->doctype('XHTML1_STRICT');
    }

    /**
     * Init Zend_Locale. Determine locale through the client's browser object.
     */
    protected function _initLocale() {
        $locale = null;

        try {
            $locale = new Zend_Locale(Zend_Locale::BROWSER);
        } catch (Zend_Locale_Exception $e) { //fallback to default locale
            $locale = new Zend_Locale('en_US');
        }

        Zend_Registry::set('Zend_Locale', $locale); //set default locale detected by browser
        Zend_Registry::set('Server_Locale', new Zend_Locale('en_US'));
    }

}
