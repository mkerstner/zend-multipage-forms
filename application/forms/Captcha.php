<?php

/**
 * @author matthias.kerstner <matthias@kerstner.at>
 */
class Form_Captcha extends Zend_Form_SubForm {

    public function init() {

        parent::init();

        $this->addElement('captcha', 'captcha', array(
            'label' => 'Please enter the verification code:',
            'captcha' => 'Image',
            'captchaOptions' => array(
                'captcha' => 'Image',
                'gcFreq' => 50,
                'expiration' => 864001,
                'wordLen' => 6,
                'font' => APPLICATION_PATH . '/../library/Fonts/verdana.ttf',
                'imgDir' => APPLICATION_PATH . '/../public/img/captcha/',
                'imgUrl' => '/img/captcha/',
                'dotNoiseLevel' => 100,
                'lineNoiseLevel' => 5
            ),
            'decorators' => array(
                'Label'
            ),
            'id' => 'captcha'
        ));
    }

}