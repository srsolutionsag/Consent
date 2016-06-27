<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

// Include ActiveRecord base class, in ILIAS >= 4.5 use ActiveRecord from Core
if (is_file('./Services/ActiveRecord/class.ActiveRecord.php')) {
    require_once('./Services/ActiveRecord/class.ActiveRecord.php');
} elseif (is_file('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecord.php')) {
    require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecord.php');
}

/**
 * Class ilConsentPlugin
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilConsentPlugin extends ilRepositoryObjectPlugin
{

    const TYPE = 'xcon';

    private static $instance;


    /**
     * @return string
     */
    public function getPluginName()
    {
        return "Consent";
    }


    protected function init()
    {
        parent::init();
        if (isset($_GET['ulx'])) {
            $this->updateLanguages();
        }
    }


    /**
     * @return ilEvaluationOverviewPlugin
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
