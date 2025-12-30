<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/ExpenseController.php';
require_once 'src/controllers/ReceiptController.php';
require_once 'src/controllers/CalendarController.php';
require_once 'src/controllers/StatsController.php';
require_once 'src/controllers/BudgetController.php';
require_once 'src/controllers/ApiController.php';
require_once 'src/controllers/AdminController.php';
require_once 'src/attributes/AttributeValidator.php';

class Routing 
{
    public static $routes = [
        'login' => ['controller' => 'SecurityController', 'action' => 'login'],
        'register' => ['controller' => 'SecurityController', 'action' => 'register'],
        'logout' => ['controller' => 'SecurityController', 'action' => 'logout'],
        
        'admin' => ['controller' => 'AdminController', 'action' => 'index'],
        'admin/add-user' => ['controller' => 'AdminController', 'action' => 'addUser'],
        'admin/block-user' => ['controller' => 'AdminController', 'action' => 'blockUser'],
        'admin/delete-user' => ['controller' => 'AdminController', 'action' => 'deleteUser'],
        'admin/toggle-admin' => ['controller' => 'AdminController', 'action' => 'toggleAdmin'],
        
        'dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
        'calendar' => ['controller' => 'CalendarController', 'action' => 'index'],
        'stats' => ['controller' => 'StatsController', 'action' => 'index'],
        'settings' => ['controller' => 'BudgetController', 'action' => 'settings'],
        
        'expenses' => ['controller' => 'ExpenseController', 'action' => 'all'],
        'expenses/daily' => ['controller' => 'ExpenseController', 'action' => 'daily'],
        'daily-expenses' => ['controller' => 'ExpenseController', 'action' => 'daily'],
        'add-expense' => ['controller' => 'ExpenseController', 'action' => 'add'],
        
        'receipt' => ['controller' => 'ReceiptController', 'action' => 'show'],
        'receipt/edit' => ['controller' => 'ReceiptController', 'action' => 'edit'],
        'receipt/delete' => ['controller' => 'ReceiptController', 'action' => 'delete'],
        
        'budget' => ['controller' => 'BudgetController', 'action' => 'budget'],
        'settings/budget' => ['controller' => 'BudgetController', 'action' => 'budget'],
        'settings/profile' => ['controller' => 'BudgetController', 'action' => 'profile'],
        'settings/password' => ['controller' => 'BudgetController', 'action' => 'profile'],
        
        'api/monthly-expenses' => ['controller' => 'ApiController', 'action' => 'monthlyExpenses'],
        'api/daily-expenses' => ['controller' => 'ApiController', 'action' => 'dailyExpenses'],
        'api/categories' => ['controller' => 'ApiController', 'action' => 'categories'],
        'api/expense/add' => ['controller' => 'ExpenseController', 'action' => 'addApi'],
        'api/receipt/update' => ['controller' => 'ReceiptController', 'action' => 'updateApi'],
    ];

    public static function run(string $path) 
    {
        if (!array_key_exists($path, self::$routes)) {
            http_response_code(404);
            include 'public/views/404.php';
            return;
        }

        $route = self::$routes[$path];
        $controllerName = $route['controller'];
        $action = $route['action'];

        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $validation = AttributeValidator::validateHttpMethod($controllerName, $action, $requestMethod);
        
        if (!$validation['allowed']) {
            http_response_code(405);
            header('Allow: ' . implode(', ', $validation['allowedMethods']));
            echo json_encode([
                'error' => true,
                'message' => $validation['message']
            ]);
            return;
        }

        $controller = new $controllerName();
        $controller->$action();
    }
}