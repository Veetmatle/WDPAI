<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
class Routing 
{
    public static $routes = [
        'login' => ['controller' => 'SecurityController', 'action' => 'login'],
        'dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
    ];

    public static function run(string $path) 
    {
        //TODO na podstawie sciezki sprawdzamy jaki HTML zwrocic
        switch ($path) 
        {
        case 'dashboard':
            // co jeśli user przekaze zmienna, jak przekazac zmienna do akcji z kontrolera
            $controller = new Routing::$routes['dashboard']['controller'];
            $action = Routing::$routes['dashboard']['action'];
            $controller->$action();
            break;
        case 'login':
            $controller = new Routing::$routes['login']['controller'];
            $action = Routing::$routes['login']['action'];
            $controller->$action();
            break;
        default:
            include 'public/views/404.html';
            break;
        } 
    }
}