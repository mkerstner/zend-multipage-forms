<?php

/**
 * CSRF protection plugin for forms.
 * 
 * @author matthias.kerstner <matthias@kerstner.at>
 */
abstract class Custom_Plugin_CsrfProtection {

    /**
     *
     * @return type
     */
    public static function getElement() {
        return new Zend_Form_Element_Hash(self::getFieldName(), array('salt' => 'unique',
                    'decorators' => array(
                        'viewHelper', array(
                            'htmlTag', array(
                                'tag' => 'dd', 'class' => 'noDisplay'
                            )
                        ),
                        'Errors'
                        )));
    }

    /**
     *
     * @param Zend_Form $form
     * @return Zend_Form 
     */
    public static function addCsrfProtection($form) {
        return $form->addElement(self::getElement());
    }

    /**
     *
     * @return string 
     */
    public static function getFieldName() {
        return Zend_Registry::get('Zend_Config')->form->csrf->element_name;
    }

}

?>
