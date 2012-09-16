<?php

class Services_RequestHandler extends Zend_Controller_Plugin_Abstract {

    public function preDispatch(Zend_Controller_Request_Abstract $request) {

        if ($request->getModuleName() == 'default') {
            $this->authenticateUiRequest($request);
        } else {
            $this->authenticateApiRequest($request);
        }
    }

    protected function authenticateUiRequest(Zend_Controller_Request_Abstract $request) {

        $layout = Zend_Layout::getMvcInstance();
        $view = $layout->getView();

        $controller = $request->getControllerName();
        $module = $request->getModuleName();

        //don't try to to authenticate if it is an api call
        if ($module != 'api') {
            $auth = Zend_Auth::getInstance();
            if (!$auth->hasIdentity()) {
                $view->logged_in = null;
                if ($controller != 'login') {
                    //$request->redirector('login');
                    $request->setControllerName('login')
                            ->setActionName('index');
                }
            } else {
                $view->logged_in = true;
            }
        }
    }

    protected function authenticateApiRequest(Zend_Controller_Request_Abstract $request) {
        

        $token = $request->getParam('access_token');
   

        if (!isset($token) && $request->getControllerName() != 'login') {
            $request->setControllerName('login')
                    ->setActionName('index');
        } else {

            if (isset($token)) {

                //access token        
                $dbAdapter = Zend_Db_Table::getDefaultAdapter();
                $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

                $authAdapter->setTableName('users')
                        ->setIdentityColumn('access_token')
                        ->setCredentialColumn('access_token');

                $authAdapter->setIdentity($token);
                $authAdapter->setCredential($token);

                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($authAdapter);

                if (!$result->isValid()) {
                    $request->setControllerName('login')
                            ->setActionName('index');
                }
            }
        }
    }

}

?>