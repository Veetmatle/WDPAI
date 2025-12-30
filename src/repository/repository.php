<?php

require_once __DIR__ . '/../../Database.php';

abstract class Repository
{
    protected Database $database;

    public function __construct()
    {
        $this->database = Database::getInstance();
    }
}