<?php

/**
 * Base class for multipage forms.
 *
 * @author matthias.kerstner <matthias@kerstner.at>
 */
class Form_MultiPage extends Zend_Form {

    /**
     * Prepare a sub form for display.
     * @param string|Zend_Form_SubForm $spec
     * @param string $actionRoute
     * @param bool? $isLastSubForm
     * @param bool? $showSubmitButton
     * @return Zend_Form_SubForm
     */
    public function prepareSubForm($spec, $actionRoute, $isLastSubForm = false, $showSubmitButton = true) {
        $subForm = null;

        if (is_string($spec)) {
            $subForm = $this->{$spec};
        } elseif ($spec instanceof Zend_Form_SubForm) {
            $subForm = $spec;
        } else {
            throw new Exception('Invalid argument passed to ' .
                    __FUNCTION__ . '()');
        }

        $this->setSubFormDecoratorsT($subForm)
                ->addSubFormAction($subForm);

        if ($showSubmitButton) {
            $this->addSubmitButton($subForm, $isLastSubForm);
        }

        $subForm->setAction($actionRoute);

        return $subForm;
    }

    /**
     * Sets default form decorators to sub form unless custom decorators have
     * been set previously.
     * @param  Zend_Form_SubForm $subForm
     * @return Form_MultiPage
     */
    public function setSubFormDecoratorsT(Zend_Form_SubForm $subForm) {
        $subForm->setDecorators(array('FormElements',
            array('HtmlTag', array('tag' => 'div',
                    'class' => 'mmsForm')),
            'Form'));

        return $this;
    }

    /**
     * Adds a submit button to @subForm.
     * @param  Zend_Form_SubForm $subForm
     * @param bool? $isLastSubForm
     * @param string? $continueLabel
     * @param string? $finishLabel
     * @return Zend_Form
     */
    public function addSubmitButton(Zend_Form_SubForm $subForm, $isLastSubForm = false, $continueLabel = 'Save and Continue', $finishLabel = 'Finish') {

        $label = $isLastSubForm ? $finishLabel : $continueLabel;

        $subForm->addElement(new Zend_Form_Element_Submit(
                        'submit',
                        array(
                            'label' => $label,
                            'required' => false,
                            'ignore' => true
                        )
        ));

        return $this;
    }

    /**
     * Adds action and method to sub form.
     * @param Zend_Form_SubForm $subForm
     * @return Zend_Form_SubForm
     */
    public function addSubFormAction(Zend_Form_SubForm $subForm) {
        $lang = Zend_Registry::get('Zend_Locale')->getLanguage();
        $view = Zend_Layout::getMvcInstance()->getView();
        $action = $view->url();

        $subForm->setAction($action)
                ->setAttrib('enctype', 'multipart/form-data')
                ->setMethod('post');
        return $this;
    }

}

?>
