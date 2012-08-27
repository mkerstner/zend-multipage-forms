<?php

/**
 * @author matthias.kerstner <matthias@kerstner.at> 
 */
class Register_Form_UserStepThree extends Zend_Form_SubForm {

    public function init() {
        $accountSubForm = new Register_Form_StepThree();
        $this->addSubForm($accountSubForm, 'stepThree');
    }

}

?>
