<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\VisitorLogModel;

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
     * @param RequestInterface|IncomingRequest $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|IncomingRequest|string|void
     */
    public function before(RequestInterface|IncomingRequest $request, $arguments = null)
    {
        // ตรวจสอบว่าไม่ใช่การเรียกไฟล์ static เช่น CSS, JS, รูปภาพ
        $uri = $request->getUri()->getPath();
        if (!preg_match('/\.(css|js|ico|png|jpg|jpeg|gif|svg)$/i', $uri)) {
            // ดึงข้อมูลผู้ใช้งาน
            $session = session();
            $userId = $session->get('user_id') ?? 0; // 0 สำหรับผู้ใช้ที่ไม่ได้ล็อกอิน

            // ดึงข้อมูลการเข้าชม
            $ipAddress = $request->getIPAddress();
            $userAgent = $request->getUserAgent()->getAgentString();
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
        //
    }
}
