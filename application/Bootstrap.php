<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initSetupEnvironment() {            
     
        
        ini_set('memory_limit', '64M');
        ini_set('session.gc_maxlifetime', '86400');
        
        define('DS', DIRECTORY_SEPARATOR);
        
        
        $this->bootstrap('view');
        $this->view = $this->getResource('view');

        $this->bootstrap('FrontController');
        $this->_front = $this->getResource('FrontController');
        

        /*
         * Autoload my libraries
         */
        
       
        //$auto_loader = Zend_Loader_Autoloader::getInstance();
        new Zend_Loader_Autoloader_Resource(
                        array(
                            'basePath' => APPLICATION_PATH,
                            'namespace' => '',
                            'resourceTypes' => array(
                                'Services' => array(
                                    'path' => DS.'..'.DS.'library'.DS,
                                    'namespace' => 'Services_'
                                )
                            )
                        )
        );
        
        
         /*
         * The library below will try to perform various other includes 
         * based on Zend include path, so it can't be loaded in the same way 
         * as the library above
         */        
        
        $path = APPLICATION_PATH.DS.'..'.DS.'library'.DS.'php_reader'.DS.'library';        
        set_include_path(get_include_path() . PATH_SEPARATOR . $path);        

    }
    
    
    protected function _initPlugins() {       
        
        $loader = new Zend_Loader_PluginLoader();
        $loader->addPrefixPath('Services_RequesHandler', 'library/');
        
        $this->_front->registerPlugin(new Services_RequestHandler());
        
    }

    protected function _initLogging() {
        $this->bootstrap('frontController');
        $logger = new Zend_Log();

        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/data/logs/app.log');

        # new Zend_Log_Writer_Firebug();
        $logger->addWriter($writer);

        $this->_logger = $logger;
        Zend_Registry::set('log', $logger);
    }

    protected function _initDB() {
        
        $ENV = APPLICATION_ENV;

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
        $credentials = $config->$ENV->resources->db;

        $this->db = Zend_Db::factory($credentials);
        // turn on profiler:
        $this->db->getProfiler()->setEnabled(true);
        Zend_Db_Table::setDefaultAdapter($this->db);
    }

    /**
     * Setup the view
     */
    protected function _initViewSettings() {

        $view = $this->view;

        $view->doctype('HTML5');
        $view->setEncoding('UTF-8');
        $view->headScript()->appendFile('/js/jquery.js');
        $view->headScript()->appendFile('/js/jplayer/jquery.jplayer.min.js');
        $view->headScript()->appendFile('/js/jplayer/add-on/jplayer.playlist.min.js');
        $view->headScript()->appendFile('/js/jqueryui/jqueryui.js');
        $view->headScript()->appendFile('/js/main.js');
        $view->headLink()->appendStylesheet('/media/themes/main/reset.css');
        $view->headLink()->appendStylesheet('/media/themes/main/main.css');
        $view->headLink()->appendStylesheet('/media/themes/main/jplayer/jplayer.css');
        $view->headLink()->appendStylesheet('/media/themes/main/jqueryui/jqueryui.css');
        $view->headLink()->headLink(array('rel' => 'shortcut icon',
            'href' => '/media/themes/main/images/favicon.ico',
            'type' => 'image/x-icon'), 'PREPEND');

        $view->headTitle('Music');
        // setting a separator string for segments:
        $view->headTitle()->setSeparator(' - ');
    }

    protected function _initMusicSettings() {

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');        
        
        $music = $config->bootstrap->resources->music;        
   
        if(!defined('MUSIC_PATH'))
            define('MUSIC_PATH', $music->path);
        
    }

}

