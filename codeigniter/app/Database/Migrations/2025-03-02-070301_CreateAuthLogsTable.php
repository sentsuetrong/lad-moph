<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthLogsTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'อ้างอิงกับตาราง users',
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'บันทึกชื่อผู้ใช้ แม้กรณีล็อกอินไม่สำเร็จ',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'comment'    => 'รองรับทั้ง IPv4 และ IPv6',
            ],
            'user_agent' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'ข้อมูลเบราว์เซอร์และระบบปฏิบัติการ',
            ],
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'ประเภทกิจกรรม: login, logout, failed_login, lockout, etc.',
            ],
            'session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'รหัส Session',
            ],
            'device_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'รหัสอุปกรณ์ใช้ในกรณี logout all devices',
            ],
            'details' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'รายละเอียดเพิ่มเติม เช่น สาเหตุการล็อกอินไม่สำเร็จ',
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'comment'    => 'เวลาที่เกิดเหตุการณ์',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('event_type');
        $this->forge->addKey('created_at');
        $this->forge->addKey('device_token');

        $this->forge->createTable('auth_logs');
    }

    public function down()
    {
        $this->forge->dropTable('auth_logs');
    }
}
