<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAdminAuth() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit;
    }
}

function checkBendaharaAuth() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'bendahara') {
        // If logged in as admin, arguably admin > bendahara, but let's keep separate for now unless requested
        // Actually often Admin has all access. Let's allow Admin to access Bendahara pages too?
        // User asked for "Bendahara Login", implying a specific role.
        // Let's stick to strict role check or allow Admin. Let's allow Admin too for maintanability.
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            return; 
        }
        
        header("Location: login.php");
        exit;
    }
}
?>
