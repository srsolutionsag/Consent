<?php

require_once __DIR__.'/../vendor/autoload.php';

use srag\DIC\DICTrait;

/**
 * Class xconUserConsent
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class xconUserConsent extends ActiveRecord
{

	use DICTrait;

	const PLUGIN_CLASS_NAME = ilConsentPlugin::class;

    const TABLE_NAME = 'xcon_user_consent';

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       8
     * @db_is_primary   true
     * @db_sequence     true
     */
    protected $id = 0;

    /**
     * ID of the corresponding ilObjConsent object
     *
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected $obj_id = 0;

    /**
     * ID of the corresponding Course object
     *
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected $course_obj_id = 0;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected $user_id = 0;

    /**
     * @var string
     *
     * @db_has_field    true
     * @db_fieldtype    timestamp
     */
    protected $created_at;

    /**
     * @var string
     *
     * @db_has_field    true
     * @db_fieldtype    timestamp
     */
    protected $updated_at;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected $status = self::STATUS_NOT_ACCEPTED;


    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected $created_user_id = 0;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected $updated_user_id = 0;


    const STATUS_NOT_ACCEPTED = 0;
    const STATUS_ACCEPTED = 1;


    /**
     * @param int $id
     */
    public function __construct($id = 0)
    {
        parent::__construct($id);
    }


    public function create()
    {
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        $this->created_user_id = self::dic()->user()->getId();
        $this->updated_user_id = self::dic()->user()->getId();
        parent::create();
    }


    public function update()
    {
        $this->updated_at = date('Y-m-d H:i:s');
        $this->updated_user_id = self::dic()->user()->getId();
        parent::update();
    }


    /**
     * Return the Name of your Database Table
     *
     * @return string
     */
    public static function returnDbTableName()
    {
        return self::TABLE_NAME;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }


    /**
     * @param int $obj_id
     */
    public function setObjId($obj_id)
    {
        $this->obj_id = $obj_id;
    }


    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }


    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }


    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }


    /**
     * @param string $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }


    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }


    /**
     * @param string $updated_at
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }


    /**
     * @return int
     */
    public function getCreatedUserId()
    {
        return $this->created_user_id;
    }


    /**
     * @param int $created_user_id
     */
    public function setCreatedUserId($created_user_id)
    {
        $this->created_user_id = $created_user_id;
    }


    /**
     * @return int
     */
    public function getUpdatedUserId()
    {
        return $this->updated_user_id;
    }


    /**
     * @param int $updated_user_id
     */
    public function setUpdatedUserId($updated_user_id)
    {
        $this->updated_user_id = $updated_user_id;
    }


    /**
     * @return int
     */
    public function getCourseObjId()
    {
        return $this->course_obj_id;
    }


    /**
     * @param int $course_obj_id
     */
    public function setCourseObjId($course_obj_id)
    {
        $this->course_obj_id = $course_obj_id;
    }


    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

}