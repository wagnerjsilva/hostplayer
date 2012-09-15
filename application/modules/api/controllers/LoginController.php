<?php

class Api_LoginController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->response = $this->getResponse();
        $this->response->setHeader('Content-Type', 'application/x-javascript', true);
        
        //exit(print_s(Zend_Auth::getInstance()->getIdentity()));
    }

    public function getAuthAdapter(array $params) {

        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

        $authAdapter->setTableName('users')
                ->setIdentityColumn('username')
                ->setCredentialColumn('password');

        $authAdapter->setIdentity($params['username']);
        $authAdapter->setCredential($params['password']);

        return $authAdapter;
    }

    public function indexAction() {

        $request = $this->getRequest();

        $username = $request->getParam('username');
        $password = $request->getParam('password');

        if (strlen($username) && strlen($password)) {

            $params = array('username' => $username, 'password' => $password);
            // Get our authentication adapter and check credentials
            $adapter = $this->getAuthAdapter($params);
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($adapter);
            if (!$result->isValid()) {
                // Invalid credentials      
                $this->view->res = json_encode(array('error' => 'Your login details are not valid', 'result' => 'bad'));
            } else {
                //exit(print_s($adapter->getResultRowObject()));
                $this->view->res = json_encode(array('result' => 'ok', 'access_token' => $adapter->getResultRowObject()->access_token));
               // exit(print_s($this->view->res));
            }
        } else {
            $this->view->res = json_encode(array('error' => 'no credentials provided', 'result' => 'bad'));
        }

        $this->render();
    }

    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();
        $this->view->res = json_encode(array('result' => 'ok'));
    }

}

