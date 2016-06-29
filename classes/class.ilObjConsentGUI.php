<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(dirname(__FILE__) . '/class.ilConsentPlugin.php');
require_once(dirname(__FILE__) . '/class.ilObjConsentAccess.php');
require_once(dirname(__FILE__) . '/class.xconUserConsent.php');
require_once(dirname(__FILE__) . '/class.xconSync.php');
require_once('./Services/UIComponent/Button/classes/class.ilSubmitButton.php');
require_once('class.xconAgreedUsersTable.php');

/**
 * Class ilObjConsentGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilObjConsentGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjConsentGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 */
class ilObjConsentGUI extends ilObjectPluginGUI
{

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilConsentPlugin
     */
    protected $pl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilObjConsent
     */
    public $object;


    /**
     * @param int $a_ref_id
     * @param int $a_id_type
     * @param int $a_parent_node_id
     */
    function __construct($a_ref_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $ilCtrl, $tpl, $ilTabs, $ilToolbar, $ilAccess, $ilUser;

        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);

        $this->ctrl = $ilCtrl;
        $this->access = $ilAccess;
        $this->tpl = $tpl;
        $this->tabs = $ilTabs;
        $this->toolbar = $ilToolbar;
        $this->pl = ilConsentPlugin::getInstance();
        $this->user = $ilUser;
        $this->tpl->addCss($this->pl->getDirectory() . '/templates/xcon.css');
    }


    /**
     * @return string
     */
    public final function getType()
    {
        return ilConsentPlugin::TYPE;
    }


    /**
     * @param string $cmd
     */
    public function performCommand($cmd)
    {
        switch ($cmd) {
            case 'showContent':
            case 'index':
                $this->index();
                $this->tabs->setTabActive('content');
                break;
            case 'edit':
                $this->edit();
                $this->tabs->setTabActive('edit');
                break;
            case 'agreed':
                $this->agreed();
                $this->tabs->setTabActive('agreed');
                break;
            case 'update':
                $this->update();
                break;
            case 'accept':
                $this->accept();
                break;
        }
    }


    /**
     * @param ilObjConsent $consent
     */
    function afterSave(ilObjConsent $consent)
    {
        global $ilAppEventHandler;
        /** @var $ilAppEventHandler ilAppEventHandler */
        $ilAppEventHandler->raise(
            'Services/Object',
            'afterSave',
            array('object' => $consent, 'obj_id' => $consent->getId(), 'obj_type' => $consent->getType()));

        parent::afterSave($consent);
    }



    /**
     * Set tabs
     */
    public function setTabs()
    {
        $this->tabs->addTab('content', $this->txt('content'), $this->ctrl->getLinkTarget($this, 'index'));
        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->tabs->addTab('agreed', $this->txt('tab_agreed'), $this->ctrl->getLinkTarget($this, 'agreed'));
            $this->tabs->addTab('edit', $this->txt('xcon_edit'), $this->ctrl->getLinkTarget($this, 'edit'));
        }
        $this->addInfoTab();
        $this->addPermissionTab();
    }


    /**
     * Show consent
     */
    protected function index()
    {
        $tpl = $this->pl->getTemplate('default/tpl.consent_index.html');
        include_once("./Services/UIComponent/Panel/classes/class.ilPanelGUI.php");
        $panel = ilPanelGUI::getInstance();
        $panel->setBody(nl2br($this->object->getLongDescription()));
        $tpl->setVariable('DESCRIPTION', $panel->getHTML());
        $tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this));
        $button = ilSubmitButton::getInstance();
        $button->setCommand('accept');
        $user_consent = $this->getUserConsent();
        $caption = ($this->isAccepted()) ? sprintf($this->txt('accepted_on'), date('d.m.Y', strtotime($user_consent->getUpdatedAt()))) : $this->txt('accept');
        if ($this->isAccepted()) {
            $button->setDisabled(true);
        }
        $button->setCaption($caption, false);
        $tpl->setVariable('BUTTON', $button->render());
        $this->tpl->setContent($tpl->get());
    }


    /**
     * Show agreed users
     */
    protected function agreed()
    {
        if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->index();
            return;
        }
        $table = new xconAgreedUsersTable($this, 'agreed', $this->object);
        $this->tpl->setContent($table->getHTML());
    }


    /**
     * Accept consent
     */
    protected function accept()
    {
        $user_consent = $this->getUserConsent();
        $sync = new xconSync($user_consent);
        if ($sync->accept()) {
            ilUtil::sendSuccess($this->txt('msg_success_accepted'), true);
        } else {
            ilUtil::sendFailure($this->txt('msg_error_accepted'), true);
        }
        $this->ctrl->setParameterByClass('ilObjCourseGUI', 'ref_id', $this->parent_id);
        $this->ctrl->redirectByClass(array('ilRepositoryGUI', 'ilObjCourseGUI'));
    }


    /**
     * @return bool
     */
    protected function isAccepted()
    {
        $user_consent = $this->getUserConsent();

        return ($user_consent->getStatus() == xconUserConsent::STATUS_ACCEPTED);
    }


    /**
     * @return xconUserConsent
     */
    protected function getUserConsent()
    {
        $user_consent = xconUserConsent::where(array(
            'user_id' => $this->user->getId(),
            'obj_id' => $this->object->getId()))
            ->first();
        if (!$user_consent) {
            $user_consent = new xconUserConsent();
            $user_consent->setUserId($this->user->getId());
            $user_consent->setObjId($this->object->getId());
            $user_consent->setCourseObjId($this->object->getCourse()->getId());
        }

        return $user_consent;
    }


    /**
     * After object has been created -> jump to this command
     */
    function getAfterCreationCmd()
    {
        return 'index';
    }


    /**
     * Get standard command
     */
    function getStandardCmd()
    {
        return 'index';
    }

    
    protected function initCreationForms($a_new_type)
    {
        $forms = parent::initCreationForms($a_new_type);
        unset($forms[self::CFORM_CLONE]);

        return $forms;
    }


    public function initCreateForm($a_new_type)
    {
        // Consent object must exist in course!
        if (ilObject::_lookupType((int) $_GET['ref_id'], true) != 'crs') {
//            throw new ilException($this->pl->txt('msg_exception_no_crs'));
            ilUtil::sendFailure($this->pl->txt('msg_error_no_crs'));
            require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
            $form = new ilPropertyFormGUI();
        } else {
            $form = parent::initCreateForm($a_new_type);
            $item = $form->getItemByPostVar('title');
            /** @var ilTextInputGUI $item */
            $item->setValue($this->txt('default_title_consent'));
            /** @var ilTextAreaInputGUI $item */
            $item = $form->getItemByPostVar('desc');
            $item->setValue($this->txt('default_description_consent'));
        }

        return $form;
    }
}
