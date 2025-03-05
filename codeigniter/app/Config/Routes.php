<?php

namespace Config;

use \CodeIgniter\Router\RouteCollection;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// ปิดการใช้งาน Auto Routing เพื่อความปลอดภัย
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// เส้นทางสาธารณะ (ไม่ต้องเข้าสู่ระบบ)
$routes->get('/', 'Home::index');

$routes->group('', function (RouteCollection $routes) {
  // เส้นทางสำหรับผู้ที่ยังไม่ได้เข้าสู่ระบบ (ใช้ guest filter)
  $routes->get('login', 'AuthController::index');
  $routes->post('login', 'AuthController::doLogin');
  $routes->get('register', 'AuthController::register');
  $routes->post('register', 'AuthController::doRegister');
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
  require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
