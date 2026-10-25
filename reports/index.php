<?php
session_start();
require_once "../includes/security.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit();
}
if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("You do not have permission to view reports.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | DoughPro</title>
    <link rel="stylesheet" href="../css/style.css?v=20261024-19">
    <link rel="stylesheet" href="../css/mobile.css?v=20261024-9" media="screen and (max-width: 850px)">
    <style>
        .report-links { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
        .report-link-card { display:block; padding:24px; border-radius:var(--radius); color:inherit; text-decoration:none; transition:transform .18s ease, box-shadow .18s ease; }
        .report-link-card:hover { transform:translateY(-2px); box-shadow:0 14px 32px rgba(28,48,34,.10); }
        .report-link-card h2 { margin:0 0 8px; }
        .report-link-card p { margin:0 0 18px; color:#68736b; }
        .report-link-card span { color:#087c43; font-weight:700; }
        @media(max-width:700px) { .report-links { grid-template-columns:1fr; } }
    </style>
<link rel="stylesheet" href="../css/brand-theme.css?v=20261024-5">
</head>
<body>
<div class="app">
    <?php $sidebarBasePath = "../"; $activePage = "reports"; require __DIR__ . "/../includes/sidebar.php"; ?>
    <main class="main-content">
        <header class="topbar"><div><h1>Reports</h1><p>Preview and export inventory and maintenance reports.</p></div></header>
        <section class="report-links" aria-label="Available reports">
            <a class="dashboard-panel report-link-card" href="../inventory/report.php">
                <h2>Inventory Report</h2>
                <p>Review current stock, critical levels, and inventory quantities.</p>
                <span>Open inventory report →</span>
            </a>
            <a class="dashboard-panel report-link-card" href="../machines/report.php">
                <h2>Maintenance Report</h2>
                <p>See each machine’s latest completed maintenance and upcoming work.</p>
                <span>Open maintenance report →</span>
            </a>
        </section>
    </main>
</div>
</body>
</html>
