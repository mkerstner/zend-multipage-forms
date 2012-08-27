<?php

/**
 * Base class for all controllers.
 * @author matthias.kerstner <matthias@kerstner.at>
 */
class Custom_Controller_Action extends Zend_Controller_Action {

    /**
     * html class for the body tag on this controller
     * @var string
     */
    protected $body_page_class;

    /**
     * The session object for the current visitor
     * @var Zend_Session_Namespace
     */
    protected $_visitor_session;

    /**
     * The account of the currently logged in user
     * @var Model_Account
     */
    protected $account;

    /**
     * Switch if a authentication is required to access actions on this controller
     * @var boolean
     */
    protected $require_authentication = false;

    /**
     *
     * @var boolean
     */
    protected $require_secure_connection = false;

    /**
     * list of actions to be excluded from $require_secure_connection check. 
     * e.g. [index, login] will exlude indexAction and loginAction from secure
     * connection check. Make sure to apply appropriate checks inside actions 
     * if required.
     * @var array
     */
    protected $actions_not_secure;

    /**
     * Mutilanguage translator
     * @var Zend_Translate
     */
    protected $_translator;

    /**
     * Current cisitors display language
     * @var unknown_type
     */
    protected $_current_language;

    /**
     * Handles ACL.
     * @param array $roles 
     *        This array must have to following format:
     *        [ 'role' => 'r|w' ]
     * 
     *        roles must must the role type, @see schema.yml ACCOUNT_TYPE_XXX
     */
    protected function checkAcl($roles = null) {
        if (!is_array($roles)) {
            throw new Custom_Exception_AccessDeniedException('You are not authorized to view this page');
        }

        $_roles = array_keys($roles);

        if (!in_array($this->account->role, $_roles)) {
            throw new Custom_Exception_AccessDeniedException('You are not authorized to view this page');
        }

        $_rights = explode('|', $roles[$this->account->role]);

        if (!in_array('r', $_rights)) {
            throw new Custom_Exception_AccessDeniedException('You are not authorized to view this page');
        }
    }

    protected function checkRequest($requireSecureConnection=false, $requireAuthentication=false) {
        $action = $this->getRequest()->getActionName();

        // init secure session if required
        if ($requireSecureConnection &&
                $this->getRequest()->getScheme() == 'http') {
            if (!is_array($this->actions_not_secure) ||
                    count($this->actions_not_secure) < 1 ||
                    !in_array($action, $this->actions_not_secure)) {
                $this->_redirect($this->getSecureUrl());
            }
        }

        // init authentication
        $account = null;
        if (Custom_Service_Authentication::isAuthenticated()) {
            $account = Custom_Service_Authentication::getAccount();
        } else {
            if ($requireAuthentication)
                Custom_Service_Authentication::forceAuthentication();
        }
    }

    /**
     *
     */
    public function init() {
        parent::init();

        $this->checkRequest($this->require_secure_connection, $this->require_authentication);

        $account = Custom_Service_Authentication::getAccount();

        // init session
        $this->_visitor_session = new Zend_Session_Namespace('visitor');
        //$this->account = ($this->_visitor_session->account) ? $this->_visitor_session->account : $account;
        if ($account) {
            $this->account = $account;
        } else if ($this->_visitor_session->account) {
            $this->account = $this->_visitor_session->account;
        } else {
            $this->account = null;
        }
        $this->view->account = $account;

        // set html body class for controller
        if ($this->body_page_class)
            $this->view->bodyPageClass = $this->body_page_class;

        $this->_translator = Zend_Registry::get('Zend_Translate');
        $this->view->currentLanguage = $this->getCurrentLanguage();
    }

    /**
     * @return Zend_Session_Namespace
     */
    public function getVisitorSession() {
        return $this->_visitor_session;
    }

    /**
     * @return Zend_Translate
     */
    public function getTranslator() {
        return $this->_translator;
    }

    /**
     * Translates the given string
     * @retrun string
     */
    public function translate($msg) {
        return $this->getTranslator()->translate($msg);
    }

    /**
     * Get the visitors account if possible
     * @return Model_Account|NULL
     */
    public function getAccount() {
        if ($this->account)
            return $this->account;
        return null;
    }

    /**
     * Get the current visitors display language
     * return string Language identifier in ISO standard
     */
    public function getCurrentLanguage() {
        if (!$this->_current_language)
            $this->_current_language = Zend_Registry::get('Zend_Locale')->getLanguage();

        return $this->_current_language;
    }

    /**
     * Add a helptext to the current view
     * @param $file The path to the file relative to the application/views/scripts/help folder (without extension)
     */
    public function addHelpText($file) {
        $this->view->helptext = $file;
    }

    /**
     * Get the address of the current site on a secure connection
     * @return string
     */
    protected function getSecureUrl() {
        $url = 'https://' . $this->getRequest()->getHttpHost() . $this->view->url();
        $getParams = $this->getRequest()->getQuery();

        if (mb_strpos($url, '?') === false)
            $url .= '?';

        $url .= http_build_query($getParams);

        return $url;
    }

}