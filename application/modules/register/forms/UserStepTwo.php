<?php

/**
 * @author matthias.kerstner <matthias@kerstner.at> 
 */
class Register_Form_UserStepTwo extends Zend_Form_SubForm {

    public function init() {
        $accountSubForm = new Register_Form_StepTwo();
        $this->addSubForm($accountSubForm, 'stepTwo');
    }

}

?>
