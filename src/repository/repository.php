<?php

require_once __DIR__ . '/../../Database.php';

/**
 * Base Repository class
 * Provides database connection for all repositories
 */
abstract class Repository
{
    protected Database $database;

    public function __construct()
    {
        $this->database = Database::getInstance();
    }
}