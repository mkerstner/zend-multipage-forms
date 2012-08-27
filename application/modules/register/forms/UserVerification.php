<?php

/**
 * @author matthias.kerstner <matthias@kerstner.at> 
 */
class Register_Form_UserVerification extends Zend_Form_SubForm {

    public function init() {

        $descriptionSubForm = new Form_SubFormDescription();
        $captchaSubForm = new Form_Captcha();

        $this->addSubForm($descriptionSubForm, 'subFormDescription');
        $this->addSubForm($captchaSubForm, 'captcha');
    }

}

?>
