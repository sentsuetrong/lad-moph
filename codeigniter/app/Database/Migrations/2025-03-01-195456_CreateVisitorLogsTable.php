<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVisitorLogsTable extends Migration
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
                'default'    => 0,
                'comment'    => '0 สำหรับผู้ใช้ที่ไม่ได้ล็อกอิน',
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
            'page' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'หน้าที่เข้าชม',
            ],
            'referrer' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'หน้าที่เข้าชมก่อนหน้านี้',
            ],
            'accessed_at' => [
                'type'       => 'DATETIME',
                'comment'    => 'เวลาที่เข้าชม',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('ip_address');
        $this->forge->addKey('accessed_at');

        $this->forge->createTable('visitor_logs');
    }

    public function down()
    {
        $this->forge->dropTable('visitor_logs');
    }
}
