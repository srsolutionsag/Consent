<?php
require_once(dirname(__FILE__) . '/class.ilConsentPlugin.php');
require_once(dirname(__FILE__) . '/class.xconUserConsent.php');
include_once("./Services/Repository/classes/class.ilObjectPlugin.php");
require_once('./Services/Tracking/interfaces/interface.ilLPStatusPlugin.php');
require_once('./Services/Tracking/classes/status/class.ilLPStatusCollection.php');
require_once('./Modules/Course/classes/class.ilObjCourse.php');

/**
 * Class ilObjConsent
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilObjConsent extends ilObjectPlugin implements ilLPStatusPluginInterface
{

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
        $this->setType(ilConsentPlugin::TYPE);
    }


    /**
     * Get all user ids with LP status completed
     *
     * @return array
     */
    public function getLPCompleted()
    {
        return xconUserConsent::where(array(
            'course_obj_id' => $this->getCourse()->getId(),
            'status' => xconUserConsent::STATUS_ACCEPTED,
        ))->getArray(null, 'user_id');
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
        return array();
    }


    /**
     * Get all user ids with LP status in progress
     *
     * @return array
     */
    public function getLPInProgress()
    {
        return array();
    }


    /**
     * Get current status for given user
     *
     * @param int $a_user_id
     * @return int
     */
    public function getLPStatusForUser($a_user_id)
    {
        $user_consent = xconUserConsent::where(array(
            'user_id' => $a_user_id,
            'obj_id' => $this->getId(),
            'status' => xconUserConsent::STATUS_ACCEPTED)
        )->first();

        return ($user_consent) ? ilLPStatus::LP_STATUS_COMPLETED_NUM : ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
    }


    /**
     * @return ilObjCourse
     */
    public function getCourse()
    {
        if ($this->course === null) {
            global $tree;
            $ref_id = $this->getRefId();
            if ($ref_id) {
                $course_ref_id = $tree->getParentId($ref_id);
            } else {
                $course_ref_id = (int) $_GET['ref_id'];
            }
            $this->course = new ilObjCourse($course_ref_id);
        }

        return $this->course;
    }
}