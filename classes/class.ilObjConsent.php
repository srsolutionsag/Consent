<?php

require_once __DIR__.'/../vendor/autoload.php';

use srag\DIC\DICTrait;

/**
 * Class ilObjConsent
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilObjConsent extends ilObjectPlugin implements ilLPStatusPluginInterface
{

	use DICTrait;

	const PLUGIN_CLASS_NAME = ilConsentPlugin::class;

    /**
     * @var ilObjCourse;
     */
    protected $course;


    /**
     * @param int $a_ref_id
     */
    public function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id);
    }


    /**
     * Get type.
     */
    public final function initType()
    {
        $this->setType(ilConsentPlugin::PLUGIN_ID);
    }


    /**
     * Get all user ids with LP status completed
     *
     * @return array
     */
    public function getLPCompleted()
    {
        return xconUserConsent::where([
            'course_obj_id' => $this->getCourse()->getId(),
            'status' => xconUserConsent::STATUS_ACCEPTED,
        ])->getArray(null, 'user_id');
    }


    /**
     * Get all user ids with LP status not attempted
     *
     * @return array
     */
    public function getLPNotAttempted()
    {
        $users_completed = $this->getLPCompleted();
        $participants = ilCourseParticipants::getInstanceByObjId($this->course->getId());
        $members = $participants->getMembers();

        return array_filter($members, function($user_id) use ($users_completed) {
            return !in_array($user_id, $users_completed);
        });
    }


    /**
     * Get all user ids with LP status failed
     *
     * @return array
     */
    public function getLPFailed()
    {
        return [];
    }


    /**
     * Get all user ids with LP status in progress
     *
     * @return array
     */
    public function getLPInProgress()
    {
        return [];
    }


    /**
     * Get current status for given user
     *
     * @param int $a_user_id
     * @return int
     */
    public function getLPStatusForUser($a_user_id)
    {
        $user_consent = xconUserConsent::where([
            'user_id' => $a_user_id,
            'obj_id' => $this->getId(),
            'status' => xconUserConsent::STATUS_ACCEPTED]
        )->first();

        return ($user_consent) ? ilLPStatus::LP_STATUS_COMPLETED_NUM : ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
    }


    /**
     * @return ilObjCourse
     */
    public function getCourse()
    {
        if ($this->course === null) {
            $ref_id = $this->getRefId();
            if ($ref_id) {
                $course_ref_id = self::dic()->tree()->getParentId($ref_id);
            } else {
                $course_ref_id = (int) filter_input(INPUT_GET, "ref_id");
            }
            $this->course = new ilObjCourse($course_ref_id);
        }

        return $this->course;
    }
}