<?php
session_start();
require 'Routing.php';

// kod 418 - czas na kawe
// MVC - monolit (widoki, css, js, php osobno itd.)
// inna architektura - frontend i backend (php, sql)
// index.php - bootstrap (front controller)

$path = trim($_SERVER['REQUEST_URI'], '/'); // wywala białe znaki
$path = parse_url($path, PHP_URL_PATH); // pobiera drugą część linka

// var_dump($path); // taki print do zmiennych

Routing::run($path);
?>