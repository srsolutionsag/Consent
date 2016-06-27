<?php

require_once(dirname(__FILE__) . '/class.ilConsentPlugin.php');
require_once(dirname(__FILE__) . '/class.ilObjConsent.php');
include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";
require_once('./Services/UIComponent/Button/classes/class.ilLinkButton.php');

/**
 * Class ilObjConsentListGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilObjConsentListGUI extends ilObjectPluginListGUI
{
	/**
	* Init type
	*/
	function initType()
	{
        $this->setType(ilConsentPlugin::TYPE);
		// Hacky: Can't overwrite init() cause this method is final -.-
		$this->cut_enabled = false;
		$this->copy_enabled = false;
		$this->link_enabled = false;
		$this->progress_enabled = false;
        global $tpl;
        $tpl->addCss(ilConsentPlugin::getInstance()->getDirectory() . '/templates/xcon.css');
	}
	
	/**
	* Get name of gui class handling the commands
	*/
	function getGuiClass()
	{
		return "ilObjConsentGUI";
	}
	
	/**
	* Get commands
	*/
	function initCommands()
	{
		return array
		(
			array(
				"permission" => "read",
				"cmd" => "showContent",
				"default" => true),
			array(
				"permission" => "write",
				"cmd" => "edit",
				"txt" => $this->txt("xcon_edit"),
				"default" => false),
		);
	}


    /**
     * @return xconUserConsent
     */
    protected function getUserConsent()
    {
        global $ilUser;

        $user_consent = xconUserConsent::where(array(
            'user_id' => $ilUser->getId(),
            'obj_id' => $this->obj_id))
            ->first();
        if (!$user_consent) {
            $user_consent = new xconUserConsent();
        }

        return $user_consent;
    }


    /**
	* Get item properties
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	function getProperties()
	{
        $consent = $this->getUserConsent();
        if ($consent->getStatus() == xconUserConsent::STATUS_ACCEPTED) {
            return array();
        }
        $button = ilLinkButton::getInstance();
        $button->setCaption($this->txt('accept'), false);
        $ref_id = array_pop(ilObject::_getAllReferences($this->obj_id));
        $this->ctrl->setParameterByClass('ilobjconsentgui', 'ref_id', $ref_id);
        $button->setUrl($this->ctrl->getLinkTargetByClass('ilobjconsentgui', 'accept'));
        return array(
			array(
				'property' => '',
				'value' => $button->render(),
			)
		);
	}


    function getDescription()
    {
        $object = new ilObjConsent($this->ref_id);

        return '<div class="xcon-list-wrapper">' . nl2br($object->getLongDescription()) . '</div>';
    }


}
