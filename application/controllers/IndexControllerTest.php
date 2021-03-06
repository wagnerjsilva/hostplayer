<?php

class Bugapp_Plugin_Initialize extends Zend_Controller_Plugin_Abstract
    {
        /**
         * @var Zend_Config
         */
        protected static $_config;
     
        /**
         * @var string Current environment
         */
        protected $_env;
     
        /**
         * @var Zend_Controller_Front
         */
        protected $_front;
     
        /**
         * @var string Path to application root
         */
        protected $_root;
     
        /**
         * Constructor
         *
         * Initialize environment, root path, and configuration.
         *
         * @param  string $env
         * @param  string|null $root
         * @return void
         */
        public function __construct($env, $root = null)
        {
            $this->_setEnv($env);
            if (null === $root) {
                $root = realpath(dirname(__FILE__) . '/../../../');
            }
            $this->_root = $root;
     
            $this->initPhpConfig();
     
            $this->_front = Zend_Controller_Front::getInstance();
        }
     
        /**
         * Route startup
         *
         * @return void
         */
        public function routeStartup(Zend_Controller_Request_Abstract $request)
        {
            $this->initDb();
            $this->initHelpers();
            $this->initView();
            $this->initPlugins();
            $this->initRoutes();
            $this->initControllers();
        }
     
        // definition of methods would follow...
    }


class IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {

    public function setUp() {
        // Assign and instantiate in one step:
        $this->bootstrap = new Zend_Application(
                        'testing',
                        APPLICATION_PATH . '/configs/application.ini'
        );
        parent::setUp();
    }

    public function tearDown() {
        $this->resetRequest();
        $this->resetResponse();
        parent::tearDown();
    }

    public function appBootstrap() {
        $this->frontController
                ->registerPlugin(new Bugapp_Plugin_Initialize('development'));
    }

    public function testHomePage() {
        $this->dispatch('/');
        $this->assertController('index');
        $this->assertAction('index');
        $this->assertXpath("//form[@action = '/foo']");
    }

}

?>
