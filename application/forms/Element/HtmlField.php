<?php

/**
 *
 * @author matthias.kerstner <matthias@kerstner.at>
 */
class Form_Element_HtmlField extends Zend_Form_Element_Xhtml {

    public $helper = 'formNote';

    public function isValid($value) {
        return true;
    }

}

?>
