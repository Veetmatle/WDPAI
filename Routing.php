<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';

class Routing 
{
    public static $routes = [
        'login' => ['controller' => 'SecurityController', 'action' => 'login'],
        'dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
        'register' => ['controller' => 'SecurityController', 'action' => 'register'],
        'logout' => ['controller' => 'SecurityController', 'action' => 'logout'],
    ];

    public static function run(string $path) 
    {
        // Sprawdź czy ścieżka istnieje w routach
        if (!array_key_exists($path, self::$routes)) {
            include 'public/views/404.html';
            return;
        }

        $route = self::$routes[$path];
        $controllerName = $route['controller'];
        $action = $route['action'];

        // Utwórz instancję kontrolera i wywołaj akcję
        $controller = new $controllerName();
        $controller->$action();
    }
}