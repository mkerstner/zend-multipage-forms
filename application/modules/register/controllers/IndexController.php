<?php

/**
 * Registration controller for user accounts.
 * 
 * @author matthias.kerstner <matthias@kerstner.at>
 */
class Register_IndexController extends Custom_Controller_MultiPage {

    protected $_formClass = 'Register_Form_User';
    protected $_formClassName = 'User';
    protected $_verificationViewScript = '_verification.phtml';
    protected $_subFormLabels = array(
        'step1' => 'Step1',
        'step2' => 'Step2',
        'step3' => 'Step3',
        'step4' => 'Verification');
    protected $_namespace = 'RegisterUserController';

    //protected $_excludeCsrfSubForms = array('step1');

    public function init() {
        parent::init();
    }

    public function indexAction() {
        parent::indexAction();
    }

    public function processAction() {
        parent::processAction();
    }

    protected function preSubFormActions($subForm, $rawPostData) {

        //$session = $this->getSessionNamespaceData();

        if ($subForm->getName() == 'step1') { // process account based on register type
            //...
        }

        return $subForm;
    }

    /**
     *
     * @param type $formName
     * @return type 
     */
    protected function checkAttachSubmitButton($formName) {
        return true;
    }

    protected function checkStep($step, Zend_Form_SubForm &$subForm, array $cleanedPostData, array $rawPostData) {

        $step = preg_replace('/[a-z]/i', '', $step);

        switch ($step) {
            case 1:
                return $this->checkStep1($subForm, $cleanedPostData, $rawPostData);
            case 2:
                return $this->checkStep2($subForm, $cleanedPostData, $rawPostData);
            case 3:
                return $this->checkStep3($subForm, $cleanedPostData, $rawPostData);
            default:
                return $cleanedPostData;
        }
    }

    private function checkStep1(Zend_Form_SubForm &$subForm, array $cleanedPostData, array $rawPostData) {
        echo 'check step1...';

        return $cleanedPostData;
    }

    private function checkStep2(Zend_Form_SubForm &$subForm, array $cleanedPostData, array $rawPostData) {
        echo 'check step2...';

        return $cleanedPostData;
    }

    private function checkStep3(Zend_Form_SubForm &$subForm, array $cleanedPostData, array $rawPostData) {
        echo 'check step3...';

        return $cleanedPostData;
    }

    /**
     * Adds account based on data stored in session.
     */
    protected function processValidForm() {
        $this->getSessionNamespace()->lock(); //lock against further registration submission
        $formData = $this->getSessionNamespaceData();

// remove session namespace
        $sessionNamespace = new Zend_Session_Namespace($this->_namespace);
        $sessionNamespace->unsetAll();
        Zend_Session::namespaceUnset($this->_namespace);
    }

}