<?php

namespace App\Models;

use CodeIgniter\Model;
use \Config\Services;
use \Config\Database;

class AuthLoggerModel extends Model
{
    protected $table            = 'auth_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id',
        'username',
        'ip_address',
        'user_agent',
        'event_type',
        'session_id',
        'device_token',
        'details',
        'created_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';

    /**
     * บันทึกกิจกรรมล็อกอิน
     */
    public function logLogin($userId, $username, $sessionId, $deviceToken = null)
    {
        $request = \Config\Services::request();

        return $this->insert([
            'user_id'      => $userId,
            'username'     => $username,
            'ip_address'   => $request->getIPAddress(),
            'user_agent'   => $request->getUserAgent()->getAgentString(),
            'event_type'   => 'login',
            'session_id'   => $sessionId,
            'device_token' => $deviceToken,
            'created_at'   => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * บันทึกกิจกรรมล็อกเอาต์
     */
    public function logLogout($userId, $username, $sessionId)
    {
        $request = \Config\Services::request();

        return $this->insert([
            'user_id'    => $userId,
            'username'   => $username,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'event_type' => 'logout',
            'session_id' => $sessionId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * บันทึกกิจกรรมล็อกเอาต์จากทุกอุปกรณ์
     */
    public function logLogoutAllDevices($userId, $username)
    {
        $request = Services::request();

        return $this->insert([
            'user_id'    => $userId,
            'username'   => $username,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'event_type' => 'logout_all_devices',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * บันทึกกิจกรรมล็อกอินไม่สำเร็จ
     */
    public function logFailedLogin($username, $reason = null)
    {
        $request = \Config\Services::request();

        return $this->insert([
            'user_id'    => null,
            'username'   => $username,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'event_type' => 'failed_login',
            'details'    => $reason,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * บันทึกกิจกรรมการล็อคบัญชี
     */
    public function logAccountLockout($username)
    {
        $request = \Config\Services::request();

        return $this->insert([
            'user_id'    => null,
            'username'   => $username,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'event_type' => 'account_lockout',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * ดึงประวัติการล็อกอิน/ล็อกเอาต์ล่าสุดของผู้ใช้
     */
    public function getRecentAuthLogs($userId, $limit = 20)
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->find();
    }

    /**
     * ดึงประวัติ session ที่ยังคงอยู่ (ล็อกอินแล้วยังไม่ล็อกเอาต์)
     */
    public function getActiveSessions($userId)
    {
        $db = Database::connect();

        // ดึงการล็อกอินทั้งหมด
        $logins = $db->table('auth_logs')
            ->select('session_id, MAX(created_at) as login_time, device_token, user_agent, ip_address')
            ->where('user_id', $userId)
            ->where('event_type', 'login')
            ->groupBy('session_id')
            ->get()
            ->getResultArray();

        // ดึงการล็อกเอาต์ทั้งหมด
        $logouts = $db->table('auth_logs')
            ->select('session_id, MAX(created_at) as logout_time')
            ->where('user_id', $userId)
            ->whereIn('event_type', ['logout', 'logout_all_devices'])
            ->groupBy('session_id')
            ->get()
            ->getResultArray();

        // สร้าง lookup table สำหรับ logouts
        $logoutLookup = [];
        foreach ($logouts as $logout) {
            $logoutLookup[$logout['session_id']] = $logout['logout_time'];
        }

        // หาสถานะเซสชันที่ยังใช้งานอยู่
        $activeSessions = [];
        foreach ($logins as $login) {
            // ถ้าไม่มีการล็อกเอาต์ หรือการล็อกอินเกิดขึ้นหลังการล็อกเอาต์
            if (
                !isset($logoutLookup[$login['session_id']]) ||
                $login['login_time'] > $logoutLookup[$login['session_id']]
            ) {
                $activeSessions[] = $login;
            }
        }

        return $activeSessions;
    }

    /**
     * ดึงสถิติการเข้าสู่ระบบในช่วง 30 วันที่ผ่านมา
     */
    public function getLoginStats($days = 30)
    {
        $db = Database::connect();

        $date = date('Y-m-d', strtotime("-{$days} days"));

        return $db->query("
            SELECT 
                DATE(created_at) as date,
                COUNT(CASE WHEN event_type = 'login' THEN 1 END) as login_count,
                COUNT(CASE WHEN event_type = 'failed_login' THEN 1 END) as failed_login_count
            FROM 
                auth_logs
            WHERE 
                created_at >= '{$date}'
            GROUP BY 
                DATE(created_at)
            ORDER BY 
                date ASC
        ")->getResultArray();
    }
}
