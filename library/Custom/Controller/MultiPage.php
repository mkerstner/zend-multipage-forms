<?php

/**
 * A base controller class for multi page forms.
 *
 * @author matthias.kerstner <matthias@kerstner.at>
 */
class Custom_Controller_MultiPage extends Zend_Controller_Action {

    protected $_formClass;
    protected $_formClassName;
    protected $_form;
    protected $_verificationViewScript;
    protected $_namespace;
    protected $_session;

    /**
     * You can specifiy custom layout scripts for each step separately. If no
     * custom step layout script is specified index.phtml will be used as 
     * default.
     * @var type 
     */
    protected $_viewScriptsForStep = array();

    /**
     * Determines which sub-forms should not have CSRF-protection
     * @var array
     */
    protected $_excludeCsrfSubForms = array();

    /**
     * Checks step (subform) based on session data.
     * @param Zend_Form_SubForm $form
     */
    protected function checkSessionStep(&$form) {
        $step = preg_replace('/[a-z]/i', '', $form->getName());
        $session = $this->getSessionNamespaceData();
        if ($step > 1 && !isset($session['step1'])) {
            //redirect user to start if previous steps have not yet been completed
            $this->_helper->redirector('index');
        }
    }

    /**
     * Default populate function. You can override this function in your concrete
     * controller.
     * @param Zend_Form_SubForm $form
     * @param array $data
     */
    protected function populateForm($form, $data) {
        /**
         * populate with data -> be aware that only fields set in
         * this array will be populated!
         */
        return $form->populate($data);
    }

    /**
     * Custom subform pre checks/actions.
     * @param Zend_Form_SubForm $subForm
     * @param type $rawPostData
     * @return Zend_Form_SubForm
     */
    protected function preSubFormActions($subForm, $rawPostData) {
        return $subForm;
    }

    /**
     * Checks whether to attach submit button.
     * @param string $formName
     * @return bool
     */
    protected function checkAttachSubmitButton($formName) {
        return true; //default action unless overriden by concrete implementation
    }

    /**
     * Checks subform identified by $step.
     * @param string $step, e.g. 'step'[NUM], e.g. "step1"
     * @param Zend_Form_SubForm $subForm
     * @param array $cleanedPostData
     * @param array $rawPostData
     * @return bool|array
     */
    protected function checkStep($step, Zend_Form_SubForm &$subForm, array $cleanedPostData, array $rawPostData) {
        $step = preg_replace('/[a-z]/i', '', $step);
        return $cleanedPostData; //TO BE IMPLEMENTED IN CONCRETE CONTROLLER
    }

    /**
     * Returns current form, i.e. form to be displayed to user based on previous action.
     * @return Zend_Form_SubForm
     */
    public function getForm() {
        if ($this->_form === null) {

            if ($this->_formClass == '')
                throw new Exception('No multipage form set');

            $this->_form = new $this->_formClass;

            if (!$this->_form)
                throw new Exception('No multipage form set');
        }

        return $this->_form;
    }

    /**
     * Get the session namespace we're using
     * @return Zend_Session_Namespace
     */
    public function getSessionNamespace() {
        if (null === $this->_session) {

            if (empty($this->_namespace))
                throw new Exception('No namespace set for multipage form');

            $this->_session = new Zend_Session_Namespace($this->_namespace);
        }

        return $this->_session;
    }

    /**
     * Returns session namespace data as associative array.
     * @return array
     */
    public function getSessionNamespaceData() {
        $data = array();
        foreach ($this->getSessionNamespace() as $k => $v) {

            if (!is_array($v))
                continue;

            foreach ($v as $kForm => $vForm) {
                $data[$k] = $vForm;
            }
        }

        return $data;
    }

    /**
     * Get a list of forms already stored in the session, i.e. those steps that
     * have been verified.
     * @return array
     */
    public function getStoredForms() {
        $stored = array();
        foreach ($this->getSessionNamespace() as $key => $value) {
            $stored[] = $key;
        }

        return $stored;
    }

    /**
     * Get list of all subforms available, i.e. the "steps".
     * @return array
     */
    public function getPotentialForms() {
        return array_keys($this->getForm()->getSubForms());
    }

    /**
     * Determines which subform was submitted.
     * @return false|Zend_Form_SubForm
     */
    public function getCurrentSubForm() {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return false;
        }

        foreach ($this->getPotentialForms() as $name) {
            $data = $request->getPost($name, false);

            if ($data) {
                if (is_array($data)) {
                    return $this->getForm()->getSubForm($name);
                    break;
                }
            }
        }

        return false;
    }

    /**
     * Returns the "next" sub form to display. If current request is not POST,
     * i.e. the page was requested directly via GET the user is redirected to
     * the first sub form. If the current subform is the last potential form it
     * will be returned again, otherwise the next sub form will be returned.
     * @return Zend_Form_SubForm|false
     */
    public function getNextSubForm() {

        if ($this->getRemainingFormCount() < 1) {
            return false; //no more subforms to process
        }

        $storedForms = $this->getStoredForms();
        $potentialForms = $this->getPotentialForms();
        $sessionData = $this->getSessionNamespaceData();
        $currentSubForm = $this->getCurrentSubForm();

        if (!$currentSubForm) { //return first subform since no form was submitted (no POST)
            return $this->getForm()->getSubForm($potentialForms[0]);
        }

        $currentSubFormName = $currentSubForm->getName();
        $currentSubFormIdx = array_search($currentSubFormName, $potentialForms);

        if ($this->isLastSubForm($currentSubFormName)) {
            return $this->getForm()->getSubForm(end($potentialForms));
        }

        return $this->getForm()->getSubForm($potentialForms[$currentSubFormIdx + 1]);
    }

    /**
     * Returns sub form specified by name. Returns getNextSubForm() if an invalid
     * name or a form has been specified that is not valid and its order is
     * greater than current sub form + 1.
     * @param string $subFormName
     * @return Zend_Form_SubForm
     */
    public function getSubForm($subFormName) {

        $storedSubForms = $this->getStoredForms();

        if (in_array($subFormName, $storedSubForms)) { //requested subform is active already
            return $this->getForm()->getSubForm($subFormName);
        }

        return $this->getCurrentSubForm();
    }

    /**
     * Checks if form is the last one of our base form.
     * @param string $subFormName
     * @return bool
     */
    public function isLastSubForm($subFormName) {
        $potentialSubForms = $this->getPotentialForms();
        return (array_search($subFormName, $potentialSubForms) >= (count($potentialSubForms) - 1));
    }

    /**
     * Returns amount of remaining sub forms not yet verified/completed.
     * @return int
     */
    public function getRemainingFormCount() {
        $storedSubForms = $this->getStoredForms();
        $potentialSubForms = $this->getPotentialForms();
        $sessionData = $this->getSessionNamespaceData();
        $completedSubForms = 0;

        foreach ($storedSubForms as $name) {
            if (isset($sessionData[$name]['metadata']['complete']) &&
                    ($sessionData[$name]['metadata']['complete'])) {
                $completedSubForms++;
            }
        }

        return (count($this->getPotentialForms()) - $completedSubForms);
    }

    /**
     * Checks if sub form is valid, i.e. valid data has been POSTed.
     * @param Zend_Form_SubForm $subForm
     * @param array $postData
     * @return bool
     */
    public function subFormIsValid(Zend_Form_SubForm $subForm, array $postData) {

        if ($subForm->isValid($postData)) {
            
            $subFormName = $subForm->getName();
            $formData = $subForm->getValues();

            // init metadata
            $formData[$subFormName]['metadata'] = array();

            // call custom checker function for subform
            $checkedFormData = $this->checkStep($subFormName, $subForm, $formData, $postData);

            if (!$checkedFormData) {
                return false; // custom subform check failed
            } else {
                $formData = $checkedFormData;
            }

            $formData[$subFormName]['metadata']['complete'] = true;

            $this->getSessionNamespace()->$subFormName = $formData; //overwrite existing values
            // activate next subform to be selectable (if not already activated)
            $potentialSubForms = $this->getPotentialForms();
            $storedSubForms = $this->getStoredForms();
            $currentSubFormIdx = array_search($subFormName, $potentialSubForms);

            // "unlock" next subform
            if ($currentSubFormIdx < (count($potentialSubForms) - 1)) {
                $nextSubFormName = $potentialSubForms[$currentSubFormIdx + 1];

                if (!isset($storedSubForms[array_search($nextSubFormName, $potentialSubForms)])) {
                    $this->getSessionNamespace()->$nextSubFormName = array();
                }
            }

            return true; //subform is valid
        }
        
        //set subset of VALID fields in session namespace
        $subSubForms = $subForm->getSubForms();
        $subFormName = $subForm->getName();
        $validSubFormFields = array();
        $subFormNames = array_keys($subSubForms);

        foreach ($subSubForms as $k => $v) {

            $elements = $v->getElements();
            $validSubFormFields[$k] = array();

            foreach ($elements as $kEl => $vEl) {
                //check if field has been posted and is valid
                //be sure to specifiy context for isValid since it is required
                //by certain validators, such as identical
                if (isset($postData[$subFormName][$k][$kEl]) &&
                        $vEl->isValid($postData[$subFormName][$k][$kEl], $postData[$subFormName][$k])) {
                    $validSubFormFields[$k][$kEl] = $vEl->getValue();
                } else {
                    //...
                }
            }
        }

        //register (partial) valid data in session namespace for subform
        $this->getSessionNamespace()->$subFormName = array($subFormName => $validSubFormFields);

        //mark as invalid (again)
        $formData[$subFormName]['metadata']['complete'] = false;

        return false;
    }

    /**
     * Checks if the *entire* form is valid, i.e. if all sub form have been validated.
     * @return bool
     */
    public function formIsValid($form, $postData) {

        if ($this->getRemainingFormCount() >= 1)
            return false;

        //final submission only allowed using submit button and not breadcrumbs
        return isset($postData[$form->getName()]['submit']);
    }

    /**
     * Produces a breadcrumb trail based on the form's current status and attaches
     * it to the subform specified.
     * Sets the following meta-fields for each breadcrumb:
     * - form: name of the subform referenced
     * - label: label to display
     * - valid: if form is valid and complete
     * - enabled: if form is enabled to be processed (i.e. previous step is valid)
     * - active: if sub form specified is currently active (i.e. is current sub form)
     * @return Zend_Form_SubForm
     */
    public function addFormBreadCrumbs($subForm) {
        $formLabels = $this->_subFormLabels;
        $storedForms = $this->getStoredForms();
        $potentialForms = $this->getPotentialForms();
        $subFormName = $subForm->getName();
        $sessionData = $this->getSessionNamespaceData();
        $breadCrumbs = array();

        foreach ($storedForms as $k => $v) { //mark enabled/valid subforms
            $breadCrumbs[] = array(
                'form' => $v,
                'label' => $formLabels[$v],
                'enabled' => true,
                'active' => false,
                'valid' => (isset($sessionData[$v]['metadata']['complete']) &&
                $sessionData[$v]['metadata']['complete']));
        }

        foreach ($potentialForms as $v) { //add incomplete/missing subforms
            if (!in_array($v, $storedForms)) {
                $breadCrumbs[] = array(
                    'form' => $v,
                    'label' => $formLabels[$v],
                    'enabled' => false,
                    'active' => false,
                    'valid' => false);
            }
        }

        //set current form-breadcrumb active
        foreach ($breadCrumbs as $k => $breadCrumb) {
            if ($breadCrumb['form'] == $subFormName) {
                $breadCrumbs[$k]['active'] = true;
            }
        }

        // sort breadcrumbs ascending
        function valSort($a, $b) {
            return strtolower($a['form']) > strtolower($b['form']);
        }

        usort($breadCrumbs, 'valSort');

        //activate first button by default
        $breadCrumbs[0]['enabled'] = true;

        $breadCrumbSubForm = new Form_BreadCrumbs();
        //1000=p at the very end of the form
        $subForm->addSubForm($breadCrumbSubForm
                        ->addBreadCrumbs($breadCrumbs), 'breadCrumbs', 1000);

        return $subForm;
    }

    /**
     * Adds CSRF protection to subform.
     * @param Zend_Form_SubForm $subForm
     * @return Zend_Form_SubForm
     */
    protected function addCsrfProtection($form) {
        if (!in_array($form->getName(), $this->_excludeCsrfSubForms)) {
            $form = Custom_Plugin_CsrfProtection::addCsrfProtection($form);
        }

        return $form;
    }

    /**
     * The last sub-form is to verify the entire form -> display form data
     * submitted once more.
     * This comes in handy if you want to users to present their data entered
     * in all sub forms once more to let them verify their input.
     * @param Zend_Form_SubForm $form
     * @return Zend_Form_SubForm
     */
    public function attachFinalFormVerification($form) {

        $potentialSubForms = $this->getPotentialForms();

        if ($form->getName() != end($potentialSubForms)) {
            return $form; // form submitted is !last subform
        }

        $invalidForms = '';
        $sessionData = $this->getSessionNamespaceData();
        $moduleName = $this->getRequest()->getModuleName();

        foreach ($potentialSubForms as $formName) {
            if ($formName != end($potentialSubForms) &&
                    (!isset($sessionData[$formName]) ||
                    !isset($sessionData[$formName]['metadata']['complete']) ||
                    !$sessionData[$formName]['metadata']['complete'])) {
                $invalidForms .= "<li>" . mb_strtoupper($moduleName) . '.' .
                        mb_strtoupper($this->_formClassName) . '.FORMSTEP.'
                        . $formName . "</li>";
            }
        }

        $html = '';

        if ($invalidForms) {
            $html .= '<div id="VerificationInvalidFormsWarningContainer">';
            $html .= 'FORM.VERIFICATION.ERRORMSG';
            $html .= '<ul>' . $invalidForms . '</ul>';
            $html .= '</div>';
        }

        $html .= '<div id="VerificationFormContainer">';
        $html .= $this->view->partialViewRenderer()->renderViewContent('index/'
                . $this->_verificationViewScript, $sessionData);
        $html .= '</div>';

        if (!$form->getSubForm('subFormDescription')) { //make sure verification subform exists
            $form->addSubForm(new Form_SubFormDescription(), 'subFormDescription');
        }

        // append html to description element
        $form->getSubForm('subFormDescription')
                ->getElement('description')->setValue(
                $form->getSubForm('subFormDescription')
                        ->getElement('description')->getValue()
                . $html);

        return $form;
    }

    /**
     * Display <form> to add private user account and POST to addAction to
     * process data.
     */
    public function indexAction() {

        // Either re-display the current page, or grab the "next"
        // (first) sub form
        if (!$form = $this->getCurrentSubForm()) {
            $form = $this->getNextSubForm();
        }

        if (!$form) { //we are already done processing the form
            return $this->render('done');
        }

        $formSessionData = $this->getSessionNamespaceData();
        $form = $this->attachFinalFormVerification($form);
        $form = $this->addFormBreadCrumbs($form);
        $form = $this->addCsrfProtection($form);
        $form = $this->getForm()->prepareSubForm($form, $this->view->linkRoute()->getUrl('register_process'), false, $this->checkAttachSubmitButton($form->getName()));
        $form = $this->populateForm($form, $formSessionData);
        $this->view->form = $form;
    }

    /**
     * This is were the actual form processing happens. Each submit button of each sub form
     * should be linked to this action as it contains the business logic of this multi page controller.
     */
    public function processAction() {

        if (!$form = $this->getCurrentSubForm()) { // no active subform
            return $this->_forward('index');
        }

        $postData = $this->getRequest()->getPost();
        $nextSubFormToLoad = null;

        // breadcrumb selected -> jump to this subform after saving current subform
        if (isset($postData[$form->getName()]['breadCrumbs'])) {
            $breadCrumbSubmit = array_keys($postData[$form->getName()]['breadCrumbs']);
            $nextSubFormToLoad = $this->getSubForm($breadCrumbSubmit[0]);
        }

        // actions/checks to be carried out PRIOR to actual validation
        $this->checkSessionStep($form);
        $form = $this->preSubFormActions($form, $postData);
        
        // check submitted subform
        if (!$this->subFormIsValid($form, $postData)) {
            
            // set next subform to load based on breadcrumb navigation
            if ($nextSubFormToLoad != null) {
                $form = $nextSubFormToLoad;
            }

            $formSessionData = $this->getSessionNamespaceData();
            $form = $this->populateForm($form, $formSessionData);

            if ($nextSubFormToLoad != null) { // validate to show errors when navigating via breadcrumbs
                $form->isValid($formSessionData);
            }

            $form = $this->preSubFormActions($form, $postData);
            $form = $this->addFormBreadCrumbs($form);
            $form = $this->attachFinalFormVerification($form);
            $form = Custom_Plugin_CsrfProtection::addCsrfProtection($form);
            $this->view->form = $this->getForm()->prepareSubForm($form, $this->view->linkRoute()->getUrl('register_process'), $this->isLastSubForm($form->getName()), $this->checkAttachSubmitButton($form->getName()));

            // set custom step view script
            if (array_key_exists($form->getName(), $this->_viewScriptsForStep)) {
                $this->view->customStepViewScript = $this->_viewScriptsForStep[$form->getName()];
            }

            return $this->render('index');
        }

        // check entire form
        if (!$this->formIsValid($form, $postData)) {

            // set next subform to load
            if ($nextSubFormToLoad != null) {
                $form = $nextSubFormToLoad; // breadcrumb navigation
            } else {
                $form = $this->getNextSubForm(); // next step
            }

            $formSessionData = $this->getSessionNamespaceData();
            $form = $this->populateForm($form, $formSessionData);

            if ($nextSubFormToLoad != null) { // validate to show errors when navigating via breadcrumbs
                $form->isValid($formSessionData);
            }

            $form = $this->preSubFormActions($form, $postData);
            $form = $this->addFormBreadCrumbs($form);
            $form = $this->attachFinalFormVerification($form);

            $form = Custom_Plugin_CsrfProtection::addCsrfProtection($form);
            $this->view->form = $this->getForm()
                    ->prepareSubForm($form, $this->view->linkRoute()->getUrl('register_process'), $this->isLastSubForm($form->getName()), $this->checkAttachSubmitButton($form->getName()));

            // set custom step view script
            if (array_key_exists($form->getName(), $this->_viewScriptsForStep)) {
                $this->view->customStepViewScript = $this->_viewScriptsForStep[$form->getName()];
            }

            return $this->render('index');
        }

        try { // valid and complete form data received
            // persist data
            $this->processValidForm();
            // unset session to prevent double submissions
            $this->getSessionNamespace()->unsetAll();
            Zend_Session::namespaceUnset($this->_namespace);
            // redirect to prevent double posting
            $this->_helper->redirector('completed', null, null);
        } catch (Exception $e) {
            throw new Exception('Failed to finalize process: ' . $e->getMessage());
        }
    }

    /**
     * Submission is complete, i.e. entire form has been verified.
     */
    public function completedAction() {
        return $this->render('done');
    }

    /**
     * This method has to be implemented in the inheriting class.
     * It contains all actions required to finally process the completed/valid form.
     * Takes data from session namespace.
     */
    protected function processValidForm() {
        throw new Exception('This method has to be implemented in the inheriting class');
    }

}

