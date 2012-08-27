<?php

/**
 *
 * @author matthias.kerstner <matthias@kerstner.at>
 */
class Form_SubFormDescription extends Zend_Form_SubForm {

    public function init() {
        $this->addElement(new Form_Element_HtmlField('description', array(
                    'decorators' => array('viewHelper', array('htmlTag',
                            array('tag' => 'div', 'id' => 'SubFormDescriptionContainer')))
                )));
    }

}

?>
