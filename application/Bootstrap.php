<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initSetupEnvironment() {            
     
        
        ini_set('memory_limit', '64M');
        ini_set('session.gc_maxlifetime', '86400');
        
        
        $this->bootstrap('view');
        $this->view = $this->getResource('view');

        $this->bootstrap('FrontController');
        $this->_front = $this->getResource('FrontController');
        

        /*
         * Autoload my libraries
         */
        $auto_loader = Zend_Loader_Autoloader::getInstance();
        $resource_loader = new Zend_Loader_Autoloader_Resource(
                        array(
                            'basePath' => APPLICATION_PATH,
                            'namespace' => '',
                            'resourceTypes' => array(
                                'Services' => array(
                                    'path' => '/../library/',
                                    'namespace' => 'Services_'
                                ),
                            )
                        )
        );

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

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
        $credentials = $config->bootstrap->resources->db;

        $this->db = Zend_Db::factory($credentials);
        // turn on profiler:
        $this->db->getProfiler()->setEnabled(true);
        Zend_Db_Table::setDefaultAdapter($this->db);
    }

   /* protected function _initAuth() {

        $this->bootstrap('view');
        $this->view = $this->getResource('view');

        $this->bootstrap('FrontController');
        $this->_front = $this->getResource('FrontController');

        $router = $this->_front->getRouter();
        $req = new Zend_Controller_Request_Http();
        $router->route($req);
        $controller = $req->getControllerName();
        $module = $req->getModuleName();

        $this->_front->setRequest($req);
        $token = $this->_front->getRequest()->getParam('access_token');

        //don't try to to authenticate if it is an api call
        if ($module != 'api') {
            $auth = Zend_Auth::getInstance();
            if (!$auth->hasIdentity()) {
                $this->view->logged_in = null;
                if ($controller != 'login') {
                    $response = new Zend_Controller_Response_Http();
                    $response->setRedirect('login');
                    $this->_front->setResponse($response);
                }
            } else {
                $this->view->logged_in = true;
            }
        }
    } */

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

        defined('MUSIC_PATH') || define(MUSIC_PATH, $music->path);
    }

}

