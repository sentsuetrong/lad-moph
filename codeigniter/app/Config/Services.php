<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Libraries\DatabaseUtility;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 * 
 * ลงทะเบียน Services ที่สร้างขึ้นเอง
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */

    /**
     * Service DatabaseUtility
     *
     * @param bool $getShared สร้าง instance ใหม่หรือใช้ instance ที่มีอยู่แล้ว
     * @param string|null $group กลุ่มฐานข้อมูลที่ต้องการใช้
     * @return DatabaseUtility
     */
    public static function databaseUtility(bool $getShared = true, ?string $group = null)
    {
        if ($getShared) {
            return static::getSharedInstance('databaseUtility', false, $group);
        }

        return new DatabaseUtility($group);
    }
}
