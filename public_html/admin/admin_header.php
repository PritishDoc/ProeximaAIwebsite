<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Proexima AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: rgba(255,255,255,0.02); border-right: 1px solid var(--border-glass); padding: 30px 20px; }
        .sidebar-logo { font-size: 1.5rem; font-family: var(--font-heading); font-weight: 700; margin-bottom: 40px; text-align: center; }
        .sidebar-nav { display: flex; flex-direction: column; gap: 10px; }
        .sidebar-nav a { padding: 12px 15px; border-radius: 8px; color: var(--text-secondary); transition: 0.3s; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(99, 102, 241, 0.1); color: var(--accent-primary); }
        .admin-content { flex: 1; padding: 40px; overflow-y: auto; background: var(--bg-dark); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 1px solid var(--border-glass); }
        
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border-glass); }
        .table th { color: var(--text-secondary); font-weight: 500; }
        .table tr:hover { background: rgba(255,255,255,0.02); }
    </style>
</head>
<body>
