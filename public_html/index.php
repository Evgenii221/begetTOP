<?php
session_start();

$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'dashboard':
        require __DIR__ . '/templates/dashboard.php';
        break;

    case 'apartments':
        require __DIR__ . '/templates/apartments.php';
        break;

    case 'meter_form':
        require __DIR__ . '/templates/meter_form.php';
        break;

    case 'meter_history':
        require __DIR__ . '/templates/meter_readings_list.php';
        break;

    case 'bills':
        require __DIR__ . '/templates/bills.php';
        break;

    case 'users':
        require __DIR__ . '/templates/users.php';
        break;

    case 'admin':
        require __DIR__ . '/templates/admin_panel.php';
        break;

    case 'tariffs':
        require __DIR__ . '/tariffs.php';
        break;

    case 'login':
        require __DIR__ . '/templates/login.php';
        break;

    case 'register':
        require __DIR__ . '/templates/register.php';
        break;

    default:
        require __DIR__ . '/templates/dashboard.php';
        break;
}