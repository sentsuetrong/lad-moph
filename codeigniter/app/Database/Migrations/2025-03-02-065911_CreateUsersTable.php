<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 3, // เจ้าหน้าที่ทั่วไปเป็นค่าเริ่มต้น
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'คำนำหน้าชื่อ',
            ],
            'fullname' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'ชื่อ-นามสกุล',
            ],
            'department' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'ชื่อกลุ่มงาน',
            ],
            'position' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'ชื่อตำแหน่งงานราชการ',
            ],
            'position_level' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'ระดับตำแหน่ง',
            ],
            'profile_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => 'default.png',
                'comment'    => 'รูปภาพโปรไฟล์',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1=กำลังใช้งาน, 0=ระงับการใช้งาน',
            ],
            'last_login' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'เวลาล็อกอินล่าสุด',
            ],
            'auth_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Token สำหรับการล็อกอิน',
            ],
            'token_created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'เวลาที่สร้าง Token',
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'deleted_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'สำหรับ soft delete',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('role_id');
        $this->forge->addKey('auth_token');

        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
