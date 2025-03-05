<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\VisitorLogModel;
use Config\Services;

class VisitorLogger implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // ตรวจสอบว่าไม่ใช่การเรียกไฟล์ static เช่น CSS, JS, รูปภาพ
        $uri = $request->getUri()->getPath();
        if (!preg_match('/\.(css|js|ico|png|jpg|jpeg|gif|svg|csv|pdf)$/i', $uri)) {
            try {
                // ใช้ DatabaseUtility Service เพื่อตรวจสอบตาราง
                $dbUtility = Services::databaseUtility();

                // ตรวจสอบว่าฐานข้อมูลพร้อมใช้งานและตาราง visitor_logs มีอยู่หรือไม่
                if ($dbUtility->isDatabaseReady() && $dbUtility->tableExists('visitor_logs')) {
                    // ดึงข้อมูลผู้ใช้งาน
                    $session = session();
                    $userId = $session->get('user_id') ?? 0; // 0 สำหรับผู้ใช้ที่ไม่ได้ล็อกอิน

                    // ดึงข้อมูลการเข้าชม
                    $ipAddress = $request->getIPAddress();

                    // แปลง RequestInterface เป็น IncomingRequest เพื่อเรียกใช้ getUserAgent()
                    // โดยเช็คก่อนว่าเป็น instance ของ IncomingRequest หรือไม่
                    $userAgent = '';
                    if ($request instanceof IncomingRequest) {
                        $userAgent = $request->getUserAgent()->getAgentString();
                    } else {
                        // กรณีที่ไม่สามารถเข้าถึง getUserAgent() ได้
                        // อาจใช้ $_SERVER ซึ่งไม่ใช่วิธีที่แนะนำ แต่ใช้เป็น fallback ได้
                        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                    }

                    $page = $request->getUri()->getPath();
                    $referrer = $request->getServer('HTTP_REFERER') ?? '';

                    // บันทึกลงฐานข้อมูล
                    $visitorLogModel = new VisitorLogModel();
                    $visitorLogModel->insert([
                        'user_id'     => $userId,
                        'ip_address'  => $ipAddress,
                        'user_agent'  => $userAgent,
                        'page'        => $page,
                        'referrer'    => $referrer,
                        'accessed_at' => date('Y-m-d H:i:s')
                    ]);
                }
                // ถ้าตารางไม่มีอยู่ แต่เป็นการเข้าถึงหน้า migration
                else if (strpos($uri, '/admin/migration') === 0) {
                    // ไม่ต้องทำอะไร ปล่อยให้เข้าถึงหน้า migration ได้
                    log_message('info', 'Accessing migration page with no tables: ' . $uri);
                }
                // ถ้าตารางไม่มีอยู่ และไม่ใช่หน้า migration
                else {
                    // อาจบันทึก log เพื่อติดตามการเข้าชมก่อนทำ migration
                    log_message('info', 'Page access before migration: ' . $uri);
                }
            } catch (\Exception $e) {
                // บันทึกข้อผิดพลาดแต่ไม่ทำให้เว็บล่ม
                log_message('error', 'VisitorLogger exception: ' . $e->getMessage());
            }
        }

        return $request;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // ส่วนนี้ไม่ได้ใช้งานสำหรับการบันทึกผู้เข้าชม
        return $response;
    }
}
