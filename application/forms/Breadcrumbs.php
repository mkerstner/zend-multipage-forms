<?php

/**
 *
 * @author matthias.kerstner <matthias@kerstner.at>
 */
class Form_BreadCrumbs extends Zend_Form_SubForm {

    /**
     *
     * @param array $breadCrumbs 
     */
    public function addBreadCrumbs($breadCrumbs) {

        foreach ($breadCrumbs as $k => $v) {

            $attribs = array('class' => 'button');

            $breadCrumb = new Zend_Form_Element_Submit(
                            $v['form'],
                            array(
                                'label' => _($v['label']),
                                'required' => false,
                                'ignore' => true,
                                'order' => $k,
                                'decorators' => array(
                                    'viewHelper',
                                    'Errors'
                            )));

            /**
             * set attributes based on status
             */
            if (!$v['enabled']) {
                $attribs['disabled'] = 'disabled';
                $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "disabled";
            } else {
                $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "enabled";
            }
            if ($v['active']) {
                $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "active";
            }
            if ($v['valid']) {
                $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "valid";
            } else {
                $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "invalid";
            }

            $breadCrumb->setAttribs($attribs);

            $this->addElement($breadCrumb);
        }

        return $this;
    }

    /**
     *
     * @param type $breadCrumbs
     * @return \Form_BreadCrumbs 
     */
    public function addBreadCrumbsToMenu($breadCrumbs) {
        $menulink = '<dt id="breadCrumbs-label">&#160;</dt><dd id="breadCrumbs-element"><fieldset id="fieldset-breadCrumbs"><dl>';
        foreach ($breadCrumbs as $k => $v) {
            $menulink .= '<input type="submit" name="step' . $k . '[breadCrumbs][step' . $k . ']" id="step' . $k . '-breadCrumbs-step' . $k . '" value="' . $v['label'] . '" class="';
            $attribs = array('class' => 'button');
            if (!$v['enabled']) {
                $attribs['disabled'] = 'disabled';
                $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "disabled";
            } else {
                $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "enabled";
            }
            if ($v['active']) {
                $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "active";
            }
            if ($v['valid']) {
                $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "valid";
            } else {
                $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "invalid";
            }
            $menulink .= $attribs['class'];
            $menulink .= '" />';
        }

        echo $menulink;
        return $this;
    }

}

?>
