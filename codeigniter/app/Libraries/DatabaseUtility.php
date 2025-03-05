<?php

namespace App\Libraries;

use \Config\Database;

/**
 * Database Utility Service
 * 
 * ให้บริการฟังก์ชันทั่วไปเกี่ยวกับฐานข้อมูล เช่น ตรวจสอบการมีอยู่ของตาราง
 */
class DatabaseUtility
{
    /**
     * ฐานข้อมูลที่จะใช้งาน
     *
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    /**
     * ประเภทของฐานข้อมูล (DBDriver)
     *
     * @var string
     */
    protected $dbDriver;

    /**
     * Constructor
     *
     * @param string $group กลุ่มของฐานข้อมูลที่จะใช้งาน (ค่าเริ่มต้นคือ default)
     */
    public function __construct(?string $group = null)
    {
        // เชื่อมต่อกับฐานข้อมูล
        $this->db = Database::connect($group);
        $this->dbDriver = $this->db->DBDriver;
    }

    /**
     * ตรวจสอบว่าตารางมีอยู่ในฐานข้อมูลหรือไม่
     * 
     * @param string $tableName ชื่อตาราง
     * @return bool true ถ้าตารางมีอยู่
     */
    public function tableExists(string $tableName): bool
    {
        // ป้องกันการ SQL Injection ด้วยการทำความสะอาดชื่อตาราง
        $tableName = $this->cleanTableName($tableName);

        // วิธีการตรวจสอบจะแตกต่างกันไปตามประเภทของฐานข้อมูล
        switch ($this->dbDriver) {
            // สำหรับ MySQL หรือ MariaDB
            case 'MySQLi':
                $query = $this->db->query("SHOW TABLES LIKE '{$tableName}'");
                return $query->getNumRows() > 0;

                // สำหรับ PostgreSQL
            case 'Postgre':
                $schema = 'public'; // เปลี่ยนตาม schema ที่ใช้
                $query = $this->db->query("SELECT to_regclass('{$schema}.{$tableName}') IS NOT NULL as exists");
                $result = $query->getRow();
                return isset($result->exists) && $result->exists;

                // สำหรับ SQLite
            case 'SQLite3':
                $query = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$tableName}'");
                return $query->getNumRows() > 0;

                // สำหรับ SQL Server
            case 'SQLSRV':
                $query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '{$tableName}'");
                return $query->getNumRows() > 0;

                // สำหรับ Oracle
            case 'OCI8':
                $query = $this->db->query("SELECT TABLE_NAME FROM USER_TABLES WHERE TABLE_NAME = '{$tableName}'");
                return $query->getNumRows() > 0;

                // สำหรับฐานข้อมูลอื่นๆ ที่ไม่ได้ระบุวิธีการเฉพาะ
            default:
                try {
                    $this->db->table($tableName)->limit(1)->get();
                    return true;
                } catch (\Exception $e) {
                    return false;
                }
        }
    }

    /**
     * ตรวจสอบว่า column มีอยู่ในตารางหรือไม่
     * 
     * @param string $tableName ชื่อตาราง
     * @param string $columnName ชื่อคอลัมน์
     * @return bool true ถ้าคอลัมน์มีอยู่
     */
    public function columnExists(string $tableName, string $columnName): bool
    {
        // ป้องกันการ SQL Injection
        $tableName = $this->cleanTableName($tableName);
        $columnName = $this->cleanTableName($columnName);

        if (!$this->tableExists($tableName)) {
            return false;
        }

        // วิธีการตรวจสอบจะแตกต่างกันไปตามประเภทของฐานข้อมูล
        switch ($this->dbDriver) {
            // สำหรับ MySQL หรือ MariaDB
            case 'MySQLi':
                $query = $this->db->query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$columnName}'");
                return $query->getNumRows() > 0;

                // สำหรับ PostgreSQL
            case 'Postgre':
                $query = $this->db->query("SELECT column_name FROM information_schema.columns WHERE table_name = '{$tableName}' AND column_name = '{$columnName}'");
                return $query->getNumRows() > 0;

                // สำหรับ SQLite
            case 'SQLite3':
                $query = $this->db->query("PRAGMA table_info('{$tableName}')");
                $columns = $query->getResultArray();
                foreach ($columns as $column) {
                    if ($column['name'] === $columnName) {
                        return true;
                    }
                }
                return false;

                // สำหรับ SQL Server
            case 'SQLSRV':
                $query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$tableName}' AND COLUMN_NAME = '{$columnName}'");
                return $query->getNumRows() > 0;

                // สำหรับ Oracle
            case 'OCI8':
                $query = $this->db->query("SELECT COLUMN_NAME FROM USER_TAB_COLUMNS WHERE TABLE_NAME = '{$tableName}' AND COLUMN_NAME = '{$columnName}'");
                return $query->getNumRows() > 0;

                // สำหรับฐานข้อมูลอื่นๆ
            default:
                try {
                    $fields = $this->db->getFieldData($tableName);
                    foreach ($fields as $field) {
                        if ($field->name === $columnName) {
                            return true;
                        }
                    }
                    return false;
                } catch (\Exception $e) {
                    return false;
                }
        }
    }

    /**
     * ทำความสะอาดชื่อตารางเพื่อป้องกัน SQL Injection
     *
     * @param string $name ชื่อตารางหรือคอลัมน์
     * @return string ชื่อที่ทำความสะอาดแล้ว
     */
    private function cleanTableName(string $name): string
    {
        // ลบอักขระพิเศษและเว้นวรรค
        return preg_replace('/[^a-zA-Z0-9_]/', '', $name);
    }

    /**
     * ตรวจสอบว่าฐานข้อมูลพร้อมใช้งานหรือไม่
     * 
     * @return bool true ถ้าฐานข้อมูลพร้อมใช้งาน
     */
    public function isDatabaseReady(): bool
    {
        try {
            // ทดสอบการเชื่อมต่อฐานข้อมูล
            $this->db->connect();
            return true;
        } catch (\Exception $e) {
            // บันทึก log ข้อผิดพลาด
            log_message('error', 'Database connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ตรวจสอบว่าตารางหลักของระบบมีอยู่ครบหรือไม่
     * 
     * @param array $coreTables รายการตารางหลักที่จำเป็น
     * @return bool true ถ้าตารางหลักมีอยู่ครบ
     */
    public function areCoreTablesReady(array $coreTables = []): bool
    {
        // ถ้าไม่ระบุตารางหลัก ให้ใช้รายการตารางหลักเริ่มต้น
        if (empty($coreTables)) {
            $coreTables = [
                'migrations',
                'users',
                'roles',
                'visitor_logs',
                'admin_access_logs',
                'auth_logs',
            ];
        }

        // ตรวจสอบทีละตาราง
        foreach ($coreTables as $table) {
            if (!$this->tableExists($table)) {
                return false;
            }
        }

        return true;
    }
}
