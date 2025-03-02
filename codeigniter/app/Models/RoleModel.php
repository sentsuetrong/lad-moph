<?php

namespace App\Models;

use CodeIgniter\Model;
use \Config\Database;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'name',
        'description',
        'permission_level'
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

    // Validation
    protected $validationRules      = [
        'name'             => 'required|min_length[3]|max_length[100]|is_unique[roles.name,id,{id}]',
        'description'      => 'permit_empty|max_length[255]',
        'permission_level' => 'required|numeric'
    ];
    protected $validationMessages   = [
        'name' => [
            'required'   => 'กรุณาระบุชื่อบทบาท',
            'min_length' => 'ชื่อบทบาทต้องมีความยาวอย่างน้อย 3 ตัวอักษร',
            'is_unique'  => 'ชื่อบทบาทนี้มีอยู่ในระบบแล้ว กรุณาใช้ชื่ออื่น',
        ],
        'permission_level' => [
            'required' => 'กรุณาระบุระดับสิทธิ์',
            'numeric'  => 'ระดับสิทธิ์ต้องเป็นตัวเลขเท่านั้น',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * ดึงบทบาททั้งหมดเรียงตามระดับสิทธิ์
     */
    public function getAllRolesSorted()
    {
        return $this->orderBy('permission_level', 'ASC')->findAll();
    }

    /**
     * ดึงบทบาทที่มีระดับสิทธิ์น้อยกว่าหรือเท่ากับที่ระบุ
     */
    public function getRolesByMinPermissionLevel($permissionLevel)
    {
        return $this->where('permission_level >=', $permissionLevel)
            ->orderBy('permission_level', 'ASC')
            ->findAll();
    }

    /**
     * ตรวจสอบว่าบทบาทนี้เป็นบทบาทระบบหรือไม่ (ไม่อนุญาตให้ลบหรือแก้ไขชื่อ)
     */
    public function isSystemRole($roleId)
    {
        $systemRoles = [1, 2, 3]; // superadmin, admin, staff
        return in_array($roleId, $systemRoles);
    }

    /**
     * ตรวจสอบว่ามีผู้ใช้งานที่ใช้บทบาทนี้อยู่หรือไม่
     */
    public function hasUsers($roleId)
    {
        $db = Database::connect();
        $count = $db->table('users')
            ->where('role_id', $roleId)
            ->countAllResults();

        return ($count > 0);
    }

    /**
     * เพิ่มบทบาทใหม่ที่มีระดับสิทธิ์น้อยกว่าหรือเท่ากับที่ระบุ
     */
    public function addRoleWithPermissionCheck($data, $permissionLevel)
    {
        // ตรวจสอบว่าระดับสิทธิ์ที่ต้องการเพิ่มน้อยกว่าหรือเท่ากับสิทธิ์ที่มี
        if ($data['permission_level'] < $permissionLevel) {
            return false;
        }

        return $this->insert($data);
    }

    /**
     * แก้ไขบทบาทโดยมีการตรวจสอบสิทธิ์
     */
    public function updateRoleWithPermissionCheck($id, $data, $permissionLevel)
    {
        // ตรวจสอบว่าบทบาทที่จะแก้ไขมี permission_level มากกว่าหรือเท่ากับของผู้แก้ไข
        $role = $this->find($id);
        if (!$role || $role['permission_level'] < $permissionLevel) {
            return false;
        }

        // ตรวจสอบว่า permission_level ที่ต้องการแก้ไขมีค่ามากกว่าหรือเท่ากับของผู้แก้ไข
        if (isset($data['permission_level']) && $data['permission_level'] < $permissionLevel) {
            return false;
        }

        // ไม่อนุญาตให้แก้ไขชื่อของบทบาทระบบ
        if ($this->isSystemRole($id) && isset($data['name'])) {
            unset($data['name']);
        }

        return $this->update($id, $data);
    }
}
