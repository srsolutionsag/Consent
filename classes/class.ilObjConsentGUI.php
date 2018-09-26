<?php

require_once __DIR__.'/../vendor/autoload.php';

use srag\DIC\DICTrait;

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

	use DICTrait;

	const PLUGIN_CLASS_NAME = ilConsentPlugin::class;

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
        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);

        self::dic()->ui()->mainTemplate()->addCss(self::plugin()->directory() . '/templates/xcon.css');
    }


    /**
     * @return string
     */
    public final function getType()
    {
        return ilConsentPlugin::PLUGIN_ID;
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
                self::dic()->tabs()->activateTab('content');
                break;
            case 'edit':
                $this->edit();
                self::dic()->tabs()->activateTab('edit');
                break;
            case 'agreed':
                $this->agreed();
                self::dic()->tabs()->activateTab('agreed');
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
     * @param ilObject $consent
     */
    function afterSave(ilObject $consent)
    {
        self::dic()->appEventHandler()->raise(
            'Services/Object',
            'afterSave',
            ['object' => $consent, 'obj_id' => $consent->getId(), 'obj_type' => $consent->getType()]);

        parent::afterSave($consent);
    }



    /**
     * Set tabs
     */
    public function setTabs()
    {
        self::dic()->tabs()->addTab('content', $this->txt('content'), self::dic()->ctrl()->getLinkTarget($this, 'index'));
        if (self::dic()->access()->checkAccess('write', '', $this->object->getRefId())) {
            self::dic()->tabs()->addTab('agreed', $this->txt('tab_agreed'), self::dic()->ctrl()->getLinkTarget($this, 'agreed'));
            self::dic()->tabs()->addTab('edit', $this->txt('xcon_edit'), self::dic()->ctrl()->getLinkTarget($this, 'edit'));
        }
        $this->addInfoTab();
        $this->addPermissionTab();
    }


    /**
     * Show consent
     */
    protected function index()
    {
        $tpl = self::plugin()->template('default/tpl.consent_index.html');
        $panel = ilPanelGUI::getInstance();
        $panel->setBody(nl2br($this->object->getLongDescription()));
        $tpl->setVariable('DESCRIPTION', $panel->getHTML());
        $tpl->setVariable('FORM_ACTION', self::dic()->ctrl()->getFormAction($this));
        $button = ilSubmitButton::getInstance();
        $button->setCommand('accept');
        $user_consent = $this->getUserConsent();
        $caption = ($this->isAccepted()) ? sprintf($this->txt('accepted_on'), date('d.m.Y', strtotime($user_consent->getUpdatedAt()))) : $this->txt('accept');
        if ($this->isAccepted()) {
            $button->setDisabled(true);
        }
        $button->setCaption($caption, false);
        $tpl->setVariable('BUTTON', $button->render());
        self::dic()->ui()->mainTemplate()->setContent($tpl->get());
    }


    /**
     * Show agreed users
     */
    protected function agreed()
    {
        if (!self::dic()->access()->checkAccess('write', '', $this->object->getRefId())) {
            $this->index();
            return;
        }
        $table = new xconAgreedUsersTable($this, 'agreed', $this->object);
        self::dic()->ui()->mainTemplate()->setContent($table->getHTML());
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
        self::dic()->ctrl()->setParameterByClass('ilObjCourseGUI', 'ref_id', $this->parent_id);
        self::dic()->ctrl()->redirectByClass(['ilRepositoryGUI', 'ilObjCourseGUI']);
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
        $user_consent = xconUserConsent::where([
            'user_id' => self::dic()->user()->getId(),
            'obj_id' => $this->object->getId()])
            ->first();
        if (!$user_consent) {
            $user_consent = new xconUserConsent();
            $user_consent->setUserId(self::dic()->user()->getId());
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
        if (ilObject::_lookupType((int) filter_input(INPUT_GET, "ref_id"), true) != 'crs') {
            ilUtil::sendFailure(self::plugin()->translate('msg_error_no_crs'));
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
