<?php

/**
 * @author matthias.kerstner <matthias@kerstner.at>
 */
class Error_IndexController extends Custom_Controller_Action {

    /**
     * 403 - Access denied
     */
    public function error403Action() {
        $errors = $this->getErrors();

        $this->getResponse()->setHttpResponseCode(403);
        $priority = Zend_Log::NOTICE;
        $this->view->message = 'Insufficient access authorisation';

        $this->logException($this->view->message, $priority, $errors);
        $this->addExceptionInformation($errors);
    }

    /**
     * 404 - Page not found
     */
    public function error404Action() {
        $errors = $this->getErrors();

        $this->getResponse()->setHttpResponseCode(404);
        $priority = Zend_Log::NOTICE;
        $this->view->message = 'Page not found';

        $this->logException($this->view->message, $priority, $errors);
        $this->addExceptionInformation($errors);
    }

    /**
     * 500 - php error
     */
    public function error500Action() {
        $errors = $this->getErrors();
        $this->getResponse()->setHttpResponseCode(500);
        $priority = Zend_Log::CRIT;
        $this->view->message = 'Application error';

        $this->logException($this->view->message, $priority, $errors);
        $this->addExceptionInformation($errors);
    }

    /**
     * 503 - Temporary unavailable
     */
    public function error503Action() {
        
    }

    /**
     * Handle exceptions and forward to responsible controller
     */
    public function indexAction() {
        $errors = $this->getErrors();

        switch (get_class($errors->exception)) {
            case 'AccessDeniedException':
            // 404 error -- controller or action not found
            //$this->_forward('error403');
            //break;
            case 'PageNotFoundException':
            case 'Zend_Controller_Dispatcher_Exception':
            case 'Zend_Controller_Action_Exception':
                // 404 error -- controller or action not found
                $this->_forward('error404');
                break;
            default:
                // application error
                $this->_forward('error500');
                break;
        }
    }

    public function getLog() {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }

    protected function getErrors() {
        $errors = $this->_getParam('error_handler');

        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }

        return $errors;
    }

    protected function logException($msg, $priority, $errors) {

        $errorsMsg = '';

        if (is_array($errors)) {
            foreach ($errorsMsg as $k => $v) {
                $errorsMsg .= ($errorsMsg != '' ? ', ' : '') . $v;
            }
        } else if ($errors instanceof ArrayObject) {
            $it = $errors->getIterator();

            while ($it->valid()) {
                $current = $it->current();

                if ($current instanceof Exception) {
                    $errorsMsg .= ($errorsMsg != '' ? ', ' : '') . $current->getMessage();
                }

                $it->next();
            }
        } else {
            $errorsMsg = $errors;
        }

        die('Exception: ' . $msg . ' (' . $errorsMsg . ')');
    }

    protected function addExceptionInformation($errors) {
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
            $this->view->exceptionClass = get_class($errors->exception);
        }
        $this->view->request = $errors->request;
    }

}

?>
