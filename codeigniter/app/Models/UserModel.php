<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'username',
        'email',
        'password',
        'role_id',
        'title',
        'fullname',
        'department',
        'position',
        'position_level',
        'profile_image',
        'is_active',
        'last_login',
        'auth_token',
        'token_created_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'username'       => 'required|min_length[5]|max_length[100]|is_unique[users.username,id,{id}]',
        'email'          => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password'       => 'required|min_length[8]',
        'role_id'        => 'required|numeric',
        'title'          => 'permit_empty|max_length[20]',
        'fullname'       => 'permit_empty|max_length[255]',
        'department'     => 'permit_empty|max_length[255]',
        'position'       => 'permit_empty|max_length[255]',
        'position_level' => 'permit_empty|max_length[50]',
        'profile_image'  => 'permit_empty|max_length[255]',
        'is_active'      => 'permit_empty|in_list[0,1]'
    ];
    protected $validationMessages   = [
        'username' => [
            'required'   => 'กรุณาระบุชื่อผู้ใช้',
            'min_length' => 'ชื่อผู้ใช้ต้องมีความยาวอย่างน้อย 5 ตัวอักษร',
            'is_unique'  => 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว กรุณาใช้ชื่อผู้ใช้อื่น',
        ],
        'email' => [
            'required'    => 'กรุณาระบุอีเมล',
            'valid_email' => 'กรุณาระบุอีเมลที่ถูกต้อง',
            'is_unique'   => 'อีเมลนี้มีอยู่ในระบบแล้ว กรุณาใช้อีเมลอื่น',
        ],
        'password' => [
            'required'   => 'กรุณาระบุรหัสผ่าน',
            'min_length' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
        }

        return $data;
    }

    /**
     * ค้นหาผู้ใช้ด้วย username และรหัสผ่าน
     */
    public function findUserByCredentials($username, $password)
    {
        $user = $this->where('username', $username)
            ->where('is_active', 1)
            ->first();

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        return $user;
    }

    /**
     * อัปเดตเวลาล็อกอินล่าสุด
     */
    public function updateLastLogin($userId)
    {
        return $this->update($userId, [
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * ค้นหาผู้ใช้ด้วย auth token
     */
    public function findUserByToken($token)
    {
        if (empty($token)) {
            return false;
        }

        return $this->where('auth_token', $token)
            ->where('is_active', 1)
            ->first();
    }

    /**
     * สร้าง auth token ใหม่
     */
    public function generateAuthToken($userId)
    {
        $token = bin2hex(random_bytes(50));

        $this->update($userId, [
            'auth_token' => $token,
            'token_created_at' => date('Y-m-d H:i:s')
        ]);

        return $token;
    }

    /**
     * ลบ auth token ออกจากทุกอุปกรณ์
     */
    public function logoutAllDevices($userId)
    {
        return $this->update($userId, [
            'auth_token' => null,
            'token_created_at' => null
        ]);
    }

    /**
     * ตรวจสอบว่าผู้ใช้เป็น superadmin หรือไม่
     */
    public function isSuperAdmin($userId)
    {
        $user = $this->select('users.*, roles.permission_level')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $userId)
            ->first();

        return ($user && $user['permission_level'] == 1);
    }

    /**
     * ตรวจสอบว่าผู้ใช้เป็น admin หรือ superadmin หรือไม่
     */
    public function isAdminOrSuperAdmin($userId)
    {
        $user = $this->select('users.*, roles.permission_level')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $userId)
            ->first();

        return ($user && $user['permission_level'] <= 2);
    }

    /**
     * ดึงผู้ใช้งานตาม role
     */
    public function getUsersByRole($roleId)
    {
        return $this->where('role_id', $roleId)
            ->findAll();
    }

    /**
     * ดึงข้อมูลผู้ใช้งานพร้อมชื่อบทบาท
     */
    public function getUsersWithRoles()
    {
        return $this->select('users.*, roles.name as role_name, roles.permission_level')
            ->join('roles', 'roles.id = users.role_id')
            ->findAll();
    }

    /**
     * ดึงข้อมูลผู้ใช้งานที่มี role_id อย่างน้อยเท่ากับค่าที่ระบุ (มีสิทธิ์น้อยกว่าหรือเท่ากับ)
     */
    public function getUsersByMinPermissionLevel($permissionLevel)
    {
        return $this->select('users.*, roles.name as role_name, roles.permission_level')
            ->join('roles', 'roles.id = users.role_id')
            ->where('roles.permission_level >=', $permissionLevel)
            ->findAll();
    }
}
