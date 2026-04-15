<?php
session_start();

$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'dashboard':
        require_once __DIR__ . '/templates/apartments.php';
        break;

    case 'apartments':
        require_once __DIR__ . '/templates/apartments.php';
        break;

    case 'meter_form':
        require_once __DIR__ . '/templates/apartments.php';
        break;

    case 'meter_history':
        require_once __DIR__ . '/templates/apartments.php';
        break;

    case 'bills':
        require_once __DIR__ . '/templates/apartments.php';
        break;

    case 'users':
        require_once __DIR__ . '/templates/apartments.php';
        break;

    case 'admin':
        require_once __DIR__ . '/templates/apartments.php';
        break;

    case 'tariffs':
        require_once __DIR__ . '/templates/apartments.php';
        break;

    case 'login':
        require_once __DIR__ . '/templates/apartments.php';
        break;

    case 'register':
        require_once __DIR__ . '/templates/apartments.php';
        break;

    default:
        require_once __DIR__ . '/templates/apartments.php';
        break;
}
