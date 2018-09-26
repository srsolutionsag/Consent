<?php

require_once __DIR__.'/../vendor/autoload.php';

use srag\RemovePluginDataConfirm\RepositoryObjectPluginUninstallTrait;

/**
 * Class ilConsentPlugin
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilConsentPlugin extends ilRepositoryObjectPlugin
{
	use RepositoryObjectPluginUninstallTrait;
	//...
	const PLUGIN_CLASS_NAME = self::class;
	const REMOVE_PLUGIN_DATA_CONFIRM_CLASS_NAME = ilConsentRemoveDataConfirm::class;
	const PLUGIN_ID = 'xcon';
    const PLUGIN_NAME = 'Consent';

	/**

	* @var self|null
	*/
	protected static $instance = NULL;


    /**
     * @return string
     */
    public function getPluginName()
    {
        return self::PLUGIN_NAME;
    }

    protected function init()
    {
        parent::init();
        $ulx = filter_input(INPUT_GET, "ulx");
        if (isset($ulx)) {
            $this->updateLanguages();
        }
    }


	/**
	 * @return self
	 */
	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @inheritdoc
	 */
	protected function deleteData()/*: void*/ {
		self::dic()->database()->dropTable(ConsentConfig::TABLE_NAME, false);
		self::dic()->database()->dropTable(xconUserConsent::TABLE_NAME, false);
	}
}
