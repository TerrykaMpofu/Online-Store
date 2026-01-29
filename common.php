<?php

session_start();
require 'db1.php';

// Require customer login
function require_customer_login() {
    if (!isset($_SESSION['cust_id'])) {
        header("Location: cust_login.php");
        exit;
    }
}

// Require employee login
function require_employee_login() {
   if (!isset($_SESSION['employee_id'])) {
        header("Location: emp_login.php");
        exit;
    }
}

    
// HTML escape helper
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
