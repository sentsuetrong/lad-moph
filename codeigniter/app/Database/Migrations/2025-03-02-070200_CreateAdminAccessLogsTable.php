<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdminAccessLogsTable extends Migration
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
                'comment'    => 'อ้างอิงกับตาราง users',
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
                'comment'    => 'หน้าที่เข้าถึงในระบบ admin',
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'การกระทำ เช่น view, create, update, delete',
            ],
            'details' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'รายละเอียดเพิ่มเติม เช่น ไอดีของข้อมูลที่แก้ไข',
            ],
            'accessed_at' => [
                'type'       => 'DATETIME',
                'comment'    => 'เวลาที่เข้าถึง',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('page');
        $this->forge->addKey('accessed_at');

        $this->forge->createTable('admin_access_logs');
    }

    public function down()
    {
        $this->forge->dropTable('admin_access_logs');
    }
}
