<?php

namespace App\Models;

use CodeIgniter\Model;
use \Config\Services;
use \Config\Database;

class AdminLoggerModel extends Model
{
    protected $table            = 'admin_acess_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id',
        'ip_address',
        'user_agent',
        'page',
        'action',
        'details',
        'accessed_at'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    /**
     * บันทึกการเข้าถึงหน้าในระบบ admin
     */
    public function logAccess($userId, $page, $action = 'view', $details = null)
    {
        $request = Services::request();

        return $this->insert([
            'user_id'     => $userId,
            'ip_address'  => $request->getIPAddress(),
            'user_agent'  => $request->getUserAgent()->getAgentString(),
            'page'        => $page,
            'action'      => $action,
            'details'     => $details,
            'accessed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * ดึงประวัติการเข้าถึงตามผู้ใช้
     */
    public function getLogsByUser($userId, $limit = 100)
    {
        return $this->where('user_id', $userId)
            ->orderBy('accessed_at', 'DESC')
            ->limit($limit)
            ->find();
    }

    /**
     * ดึงประวัติการเข้าถึงทั้งหมด
     */
    public function getAllLogs($limit = 1000)
    {
        return $this->select('admin_access_logs.*, users.username, users.fullname')
            ->join('users', 'users.id = admin_access_logs.user_id')
            ->orderBy('accessed_at', 'DESC')
            ->limit($limit)
            ->find();
    }

    /**
     * ดึงประวัติการเข้าถึงตามช่วงเวลา
     */
    public function getLogsByDateRange($startDate, $endDate)
    {
        return $this->select('admin_access_logs.*, users.username, users.fullname')
            ->join('users', 'users.id = admin_access_logs.user_id')
            ->where('accessed_at >=', $startDate)
            ->where('accessed_at <=', $endDate)
            ->orderBy('accessed_at', 'DESC')
            ->find();
    }

    /**
     * ดึงสถิติการเข้าถึงแยกตามหน้า
     */
    public function getPageAccessStats($days = 30)
    {
        $db = Database::connect();

        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        return $db->table('admin_access_logs')
            ->select('page, COUNT(*) as access_count')
            ->where('accessed_at >=', $date)
            ->groupBy('page')
            ->orderBy('access_count', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * ดึงสถิติการเข้าถึงแยกตามผู้ใช้
     */
    public function getUserAccessStats($days = 30)
    {
        $db = Database::connect();

        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        return $db->table('admin_access_logs')
            ->select('admin_access_logs.user_id, users.username, users.fullname, COUNT(*) as access_count')
            ->join('users', 'users.id = admin_access_logs.user_id')
            ->where('accessed_at >=', $date)
            ->groupBy('user_id')
            ->orderBy('access_count', 'DESC')
            ->get()
            ->getResultArray();
    }
}
