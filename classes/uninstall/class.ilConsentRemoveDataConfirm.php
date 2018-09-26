<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use srag\RemovePluginDataConfirm\AbstractRemovePluginDataConfirm;

/**
 * Class ilConsentRemoveDataConfirm
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 * @ilCtrl_isCalledBy ilConsentRemoveDataConfirm: ilUIPluginRouterGUI
 */

class ilConsentRemoveDataConfirm extends AbstractRemovePluginDataConfirm {

	const PLUGIN_CLASS_NAME = ilConsentPlugin::class;

	/**
	 * @inheritdoc
	 */
	public function getUninstallRemovesData()/*: ?bool*/ {
		return ConsentConfig::getUninstallRemovesData();
	}


	/**
	 * @inheritdoc
	 */
	public function setUninstallRemovesData(/*bool*/$uninstall_removes_data)/*: void*/ {
		ConsentConfig::setUninstallRemovesData($uninstall_removes_data);
	}


	/**
	 * @inheritdoc
	 */
	public function removeUninstallRemovesData()/*: void*/ {
		ConsentConfig::removeUninstallRemovesData();
	}
}