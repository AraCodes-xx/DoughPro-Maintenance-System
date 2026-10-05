<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>DoughPro | Dashboard</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<div class="app">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="logo">

            <h2>DoughPro</h2>

            <span>
                Maintenance System
            </span>

        </div>


        <nav>

            <!-- DASHBOARD -->
            <a
                href="dashboard.php"
                class="active"
            >
                🏠 Dashboard
            </a>


            <!-- INVENTORY -->
            <a href="inventory/index.php">
                📦 Inventory
            </a>


            <!-- MAINTENANCE -->
            <a href="maintenance/index.php">
                🔧 Maintenance
            </a>


            <!-- HISTORY -->
            <a href="history/index.php">
                📋 History
            </a>

        </nav>


        <div class="sidebar-bottom">

            <div class="admin-info">

                <strong>
                    <?= htmlspecialchars($_SESSION["full_name"]) ?>
                </strong>

                <small>
                    Administrator
                </small>

            </div>


            <a
                href="logout.php"
                class="logout"
            >
                🚪 Logout
            </a>

        </div>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-content">


        <!-- TOPBAR -->

        <header class="topbar">

            <div>

                <h1>
                    Dashboard
                </h1>

                <p>
                    Welcome back,
                    <?= htmlspecialchars($_SESSION["full_name"]) ?>.
                </p>

            </div>

        </header>


        <!-- DASHBOARD CARDS -->

        <section class="dashboard-cards">


            <div class="dashboard-card">

                <span>
                    Total Inventory
                </span>

                <strong>
                    0
                </strong>

                <small>
                    Items being monitored
                </small>

            </div>


            <div class="dashboard-card">

                <span>
                    Critical Stock
                </span>

                <strong>
                    0
                </strong>

                <small>
                    Items need attention
                </small>

            </div>


            <div class="dashboard-card">

                <span>
                    Upcoming Maintenance
                </span>

                <strong>
                    0
                </strong>

                <small>
                    Scheduled tasks
                </small>

            </div>


            <div class="dashboard-card">

                <span>
                    Overdue
                </span>

                <strong>
                    0
                </strong>

                <small>
                    Tasks requiring attention
                </small>

            </div>

        </section>


        <!-- DASHBOARD CONTENT -->

        <section class="dashboard-grid">


            <!-- CRITICAL INVENTORY -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <h2>
                        Critical Inventory
                    </h2>

                    <a href="inventory/index.php">
                        View Inventory
                    </a>

                </div>


                <div class="empty-state">

                    <p>
                        No critical inventory items.
                    </p>

                </div>

            </div>


            <!-- UPCOMING MAINTENANCE -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <h2>
                        Upcoming Maintenance
                    </h2>

                    <a href="maintenance/index.php">
                        View Schedule
                    </a>

                </div>


                <div class="empty-state">

                    <p>
                        No upcoming maintenance.
                    </p>

                </div>

            </div>


        </section>


    </main>

</div>

</body>

</html>