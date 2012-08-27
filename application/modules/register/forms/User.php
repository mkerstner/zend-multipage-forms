<?php

/**
 * @author matthias.kerstner <matthias@kerstner.at> 
 */
class Register_Form_User extends Form_MultiPage {

    /**
     * Setup the sub-forms for this multi-page form.
     */
    public function init() {
        parent::init();

        $step1SubForm = new Register_Form_UserStepOne();
        $step2SubForm = new Register_Form_UserStepTwo();
        $step3SubForm = new Register_Form_UserStepThree();
        $verificationSubForm = new Register_Form_UserVerification();

        $this->addSubForms(array(
            'step1' => $step1SubForm,
            'step2' => $step2SubForm,
            'step3' => $step3SubForm,
            'step4' => $verificationSubForm
        ));
    }

}

?>