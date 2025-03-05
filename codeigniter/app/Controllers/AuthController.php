<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\UserModel;
use App\Models\AuthLoggerModel;

class AuthController extends BaseController
{
    protected $userModel;
    protected $authLoggerModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->authLoggerModel = new AuthLoggerModel();
    }

    public function index()
    {
        $redirectUrl = session()->getTempdata('redirect_url');

        return view('auth/login', [
            'title' => 'เข้าสู่ระบบ',
            'redirect' => $this->request->getGet('redirect') ?? $redirectUrl
        ]);
    }

    /**
     * ตรวจสอบการล็อกอิน
     */
    public function doLogin()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->with('error', 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน')
                ->withInput();
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $rememberMe = $this->request->getPost('remember_me') == '1';

        // ตรวจสอบข้อมูลผู้ใช้
        $user = $this->userModel->findUserByCredentials($username, $password);

        if (!$user) {
            // บันทึกการล็อกอินไม่สำเร็จ
            $this->authLoggerModel->logFailedLogin($username, 'Invalid credentials');

            // ตรวจสอบจำนวนการล็อกอินไม่สำเร็จติดต่อกัน
            $failedAttempts = $this->getFailedLoginAttempts($username);

            if ($failedAttempts >= 5) {
                // ล็อคบัญชีชั่วคราว
                $this->authLoggerModel->logAccountLockout($username);

                return redirect()->back()
                    ->with('error', 'บัญชีของคุณถูกล็อคชั่วคราวเนื่องจากการล็อกอินไม่สำเร็จหลายครั้ง โปรดลองอีกครั้งในอีก 15 นาที')
                    ->withInput(['username' => $username]);
            }

            return redirect()->back()
                ->with('error', 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง')
                ->withInput(['username' => $username]);
        }

        // ตรวจสอบว่าบัญชีถูกระงับหรือไม่
        if ($user['is_active'] != 1) {
            $this->authLoggerModel->logFailedLogin($username, 'Account suspended');

            return redirect()->back()
                ->with('error', 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ')
                ->withInput(['username' => $username]);
        }

        // สร้าง session
        $session = session();
        $sessionId = session_id();

        // สร้าง device token สำหรับการล็อกเอาต์จากทุกอุปกรณ์
        $deviceToken = md5($this->request->getUserAgent()->getAgentString() . time());

        // ถ้าเลือก "จำฉันไว้"
        if ($rememberMe) {
            // สร้าง auth token
            $token = $this->userModel->generateAuthToken($user['id']);

            // เก็บ cookie สำหรับการล็อกอินอัตโนมัติ (30 วัน)
            set_cookie('auth_token', $token, 30 * 24 * 60 * 60);
            set_cookie('device_token', $deviceToken, 30 * 24 * 60 * 60);
        }

        // เก็บข้อมูลใน session
        $sessionData = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'fullname' => $user['fullname'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'profile_image' => $user['profile_image'],
            'device_token' => $deviceToken,
            'is_logged_in' => true
        ];

        $session->set($sessionData);

        // อัปเดตเวลาล็อกอินล่าสุด
        $this->userModel->updateLastLogin($user['id']);

        // บันทึกการล็อกอิน
        $this->authLoggerModel->logLogin($user['id'], $user['username'], $sessionId, $deviceToken);

        // ตรวจสอบว่ามี redirect URL หรือไม่
        $redirect = $this->request->getPost('redirect');
        if (!empty($redirect)) {
            return redirect()->to($redirect);
        }

        // ถ้าเป็นแอดมิน ให้ไปที่หน้า admin dashboard
        if ($this->userModel->isAdminOrSuperAdmin($user['id'])) {
            return redirect()->to('/admin/dashboard');
        }

        // ถ้าเป็นผู้ใช้ทั่วไป ให้ไปที่หน้า dashboard
        return redirect()->to('/dashboard');
    }

    /**
     * ดึงจำนวนการล็อกอินไม่สำเร็จติดต่อกัน
     * @param string $username ชื่อผู้ใช้
     * @return int จำนวนการล็อกอินไม่สำเร็จติดต่อกัน
     */
    private function getFailedLoginAttempts($username)
    {
        $db = \Config\Database::connect();

        // ตรวจสอบการล็อกอินครั้งล่าสุด
        $lastLogin = $db->table('auth_logs')
            ->where('username', $username)
            ->where('event_type', 'login')
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        // ถ้ามีการล็อกอินล่าสุด ให้นับการล็อกอินไม่สำเร็จหลังจากนั้น
        if ($lastLogin) {
            $failedAttempts = $db->table('auth_logs')
                ->where('username', $username)
                ->where('event_type', 'failed_login')
                ->where('created_at >', $lastLogin['created_at'])
                ->countAllResults();
        } else {
            // ถ้าไม่มีการล็อกอินล่าสุด ให้นับการล็อกอินไม่สำเร็จทั้งหมดใน 15 นาทีล่าสุด
            $failedAttempts = $db->table('auth_logs')
                ->where('username', $username)
                ->where('event_type', 'failed_login')
                ->where('created_at >', date('Y-m-d H:i:s', time() - 900)) // 15 นาที
                ->countAllResults();
        }

        return $failedAttempts;
    }

    /**
     * ตรวจสอบว่าบัญชีถูกล็อคหรือไม่
     * @param string $username ชื่อผู้ใช้
     * @return bool true ถ้าบัญชีถูกล็อค
     */
    private function isAccountLocked($username)
    {
        $db = \Config\Database::connect();

        // ตรวจสอบว่ามีการล็อคบัญชีในช่วง 15 นาทีล่าสุดหรือไม่
        $lockout = $db->table('auth_logs')
            ->where('username', $username)
            ->where('event_type', 'account_lockout')
            ->where('created_at >', date('Y-m-d H:i:s', time() - 900)) // 15 นาที
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return !empty($lockout);
    }
}
