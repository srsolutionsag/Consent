<?php

require_once('./Services/Tracking/classes/class.ilLPStatusWrapper.php');

/**
 * Class xconSync
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class xconSync
{

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
        global $ilUser;

        // Currently only the user herself can accept...
        if ($this->consent->getUserId() != $ilUser->getId()) {
            return false;
        }
        $this->consent->setStatus(xconUserConsent::STATUS_ACCEPTED);
        $this->consent->save();
        ilLPStatusWrapper::_updateStatus($this->consent->getObjId(), $this->consent->getUserId());

        return true;
    }

}