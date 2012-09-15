<?php
class RefreshController extends Zend_Controller_Action
{

    public function init()
    {        
      
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        $this->reloader = new Application_Model_LibraryLoader();
        $this->reloader->reload();
        $this->reloader->removeDeadFileLinksFromDb();      
    }

    public function indexAction()
    {
        
             
        
    }

}

