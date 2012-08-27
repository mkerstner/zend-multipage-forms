<?php

/**
 * @author matthias.kerstner <matthias@kerstner.at> 
 */
class Register_Form_UserStepOne extends Zend_Form_SubForm {

    public function init() {
        $accountSubForm = new Register_Form_StepOne();
        $this->addSubForm($accountSubForm, 'stepOne');
    }

}

?>
