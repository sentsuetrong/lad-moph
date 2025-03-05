<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $uri = $this->request->getUri();
        $path = $uri->getPath();

        $data = [
            'uri' => $uri,
            'path' => $path,
            'isAdminPage' => preg_match('/\/admin/i', $path)
        ];

        return view('welcome_message', $data);
    }

    public function test()
    {
        $routes = \Config\Services::routes();
        echo '<pre>';
        var_dump($routes->getRoutes());
        echo '</pre>';
    }
}
