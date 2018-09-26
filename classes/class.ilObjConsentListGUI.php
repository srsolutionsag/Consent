<?php

require_once __DIR__.'/../vendor/autoload.php';

use srag\DIC\DICTrait;

/**
 * Class ilObjConsentListGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilObjConsentListGUI extends ilObjectPluginListGUI
{
	use DICTrait;

	const PLUGIN_CLASS_NAME = ilConsentPlugin::class;

	/**
	* Init type
	*/
	function initType()
	{
        $this->setType(ilConsentPlugin::PLUGIN_ID);
		// Hacky: Can't overwrite init() cause this method is final -.-
		$this->cut_enabled = false;
		$this->copy_enabled = false;
		$this->link_enabled = false;
		$this->progress_enabled = false;
        self::dic()->ui()->mainTemplate()->addCss(self::plugin()->directory() . '/templates/xcon.css');
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
		return
		[
			[
				"permission" => "read",
				"cmd" => "showContent",
				"default" => true],
			[
				"permission" => "write",
				"cmd" => "edit",
				"txt" => $this->txt("xcon_edit"),
				"default" => false],
		];
	}


    /**
     * @return xconUserConsent
     */
    protected function getUserConsent()
    {
        $user_consent = xconUserConsent::where([
            'user_id' => self::dic()->user()->getId(),
            'obj_id' => $this->obj_id])
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
            return [];
        }
        $button = ilLinkButton::getInstance();
        $button->setCaption($this->txt('accept'), false);
        $ref_id = array_pop(ilObject::_getAllReferences($this->obj_id));
        $this->ctrl->setParameterByClass('ilobjconsentgui', 'ref_id', $ref_id);
        $button->setUrl($this->ctrl->getLinkTargetByClass('ilobjconsentgui', 'accept'));
        return [
			[
				'property' => '',
				'value' => $button->render(),
			]
        ];
	}


    function getDescription()
    {
        $object = new ilObjConsent($this->ref_id);

        return '<div class="xcon-list-wrapper">' . nl2br($object->getLongDescription()) . '</div>';
    }


}
