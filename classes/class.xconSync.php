<?php

require_once __DIR__.'/../vendor/autoload.php';

use srag\DIC\DICTrait;

/**
 * Class xconSync
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class xconSync
{

	use DICTrait;

	const PLUGIN_CLASS_NAME = ilConsentPlugin::class;

    /**
     * @var xconUserConsent
     */
    protected $consent;

    /**
     * @param xconUserConsent $consent
     */
    public function __construct(xconUserConsent $consent)
    {
        $this->consent = $consent;
    }


    /**
     * Accept consent and sync with learning progress
     */
    public function accept()
    {
        // Currently only the user herself can accept...
        if ($this->consent->getUserId() != self::dic()->user()->getId()) {
            return false;
        }
        $this->consent->setStatus(xconUserConsent::STATUS_ACCEPTED);
        $this->consent->save();
        ilLPStatusWrapper::_updateStatus($this->consent->getObjId(), $this->consent->getUserId());

        return true;
    }

}