<?php

/**
 * @author matthias.kerstner <matthias@kerstner.at> 
 */
class IndexController extends Zend_Controller_Action {

    public function indexAction() {
        $this->_redirect('/register/');
    }

}

