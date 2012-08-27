<?php

/**
 * @author matthias.kerstner <matthias@kerstner.at> 
 */
class Register_Form_StepTwo extends Zend_Form_SubForm {

    public function init() {
        parent::init();

        $this->addElement('text', 'step2', array(
            'filters' => array('StringTrim'),
            'decorators' => array(
                'viewHelper', array(
                    'Label', array('class' => 'label required')
                ),
                'Errors'
            ),
            'validators' => array(
                array('StringLength', true, array(1, 255))),
            'required' => true,
            'label' => 'Step 2',
            'id' => 'StepTwo'
        ));
    }

}

?>