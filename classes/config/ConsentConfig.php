<?php

use srag\ActiveRecordConfig\ActiveRecordConfig;

/**
 * Class ConsentConfig
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ConsentConfig extends ActiveRecordConfig {

	const TABLE_NAME = "xcon_config";
	const PLUGIN_CLASS_NAME = ilConsentPlugin::class;

	/**
	 * @return bool|null
	 */
	public static function getUninstallRemovesData()/*: ?bool*/ {
		return self::getXValue(ilConsentRemoveDataConfirm::KEY_UNINSTALL_REMOVES_DATA, ilConsentRemoveDataConfirm::DEFAULT_UNINSTALL_REMOVES_DATA);
	}


	/**
	 * @param bool $uninstall_removes_data
	 */
	public static function setUninstallRemovesData(/*bool*/$uninstall_removes_data)/*: void*/ {
		self::setBooleanValue(ilConsentRemoveDataConfirm::KEY_UNINSTALL_REMOVES_DATA, $uninstall_removes_data);
	}

	/**
	 *
	 */
	public static function removeUninstallRemovesData()/*: void*/ {
		self::removeName(ilConsentRemoveDataConfirm::KEY_UNINSTALL_REMOVES_DATA);
	}

}