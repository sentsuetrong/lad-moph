<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitorLogModel extends Model
{
    protected $table         = 'visitor_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id',
        'ip_address',
        'user_agent',
        'page',
        'referrer',
        'accessed_at'
    ];

    // ไม่ต้องใช้ timestamps ของ CI เพราะเรามี accessed_at เป็นเวลาเข้าชม
    protected $useTimestamps = false;

    // Validation rules
    protected $validationRules = [
        'ip_address'  => 'required|max_length[45]',
        'page'        => 'required|max_length[255]',
        'accessed_at' => 'required'
    ];

    /**
     * ดึงข้อมูลการเข้าชมตามช่วงเวลา
     */
    public function getVisitorsByDateRange($startDate, $endDate)
    {
        return $this->where('accessed_at >=', $startDate)
            ->where('accessed_at <=', $endDate)
            ->findAll();
    }

    /**
     * ดึงสถิติการเข้าชมเว็บไซต์
     */
    public function getVisitorStats()
    {
        $db = \Config\Database::connect();

        // จำนวนผู้เข้าชมทั้งหมด
        $totalVisitors = $this->countAllResults();

        // จำนวนผู้เข้าชมวันนี้
        $todayVisitors = $this->where('DATE(accessed_at)', date('Y-m-d'))
            ->countAllResults();

        // หน้าที่มีการเข้าชมมากที่สุด
        $popularPages = $db->table('visitor_logs')
            ->select('page, COUNT(*) as visit_count')
            ->groupBy('page')
            ->orderBy('visit_count', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return [
            'total_visitors' => $totalVisitors,
            'today_visitors' => $todayVisitors,
            'popular_pages'  => $popularPages
        ];
    }
}
