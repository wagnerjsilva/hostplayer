<?php

class Api_MusicController extends Zend_Controller_Action {

    public function init() {
        /*
         * Servers Ajax content
         */
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->response = $this->getResponse();
        $this->response->setHeader('Content-Type', 'application/x-javascript', true);
        $this->music = new Application_Model_Music();
    }

    public function indexAction() {
        $this->view->music = $this->res->getAllTracks();
    }

    public function searchAction() {

        $term = $this->_request->getParam('term');
        $res = $this->getTracks($term);
        $this->view->res = json_encode($res);

        $this->render('index');
    }

    protected function getTracks($term) {    
        
        if (strlen($term) < 1) {
            $res = array("results" => $this->music->getAllTracks());
        } else {
            $res = array("results" => $this->music->getSongsUsingSearchTerm($term));
        }
        
        return $res;
    }

}

