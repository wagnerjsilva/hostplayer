<?php

class DownloadController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->music = new Application_Model_Music();
    }

    public function indexAction() {

        $id = $this->_request->getParam('song');
        $res = $this->music->getTrackUsingId($id);
        $res = $res[0];        
        $this->music->smartReadFile($res['file'], basename($res['file']));
    }

   
}

?>