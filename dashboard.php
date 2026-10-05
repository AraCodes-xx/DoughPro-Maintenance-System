<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| INVENTORY SUMMARY
|--------------------------------------------------------------------------
*/

// Total inventory items
$inventoryQuery = $conn->query(
    "SELECT COUNT(*) AS total
     FROM inventory"
);

$inventoryData = $inventoryQuery->fetch_assoc();

$totalInventory = (int)$inventoryData["total"];


// Critical inventory items
$criticalQuery = $conn->query(
    "SELECT COUNT(*) AS total
     FROM inventory
     WHERE quantity <= critical_level"
);

$criticalData = $criticalQuery->fetch_assoc();

$criticalStock = (int)$criticalData["total"];


/*
|--------------------------------------------------------------------------
| MAINTENANCE SUMMARY
|--------------------------------------------------------------------------
*/

// Upcoming maintenance
$upcomingQuery = $conn->query(
    "SELECT COUNT(*) AS total
     FROM maintenance_schedules
     WHERE status = 'pending'
     AND scheduled_date >= CURDATE()"
);

$upcomingData = $upcomingQuery->fetch_assoc();

$upcomingMaintenance = (int)$upcomingData["total"];


// Overdue maintenance
$overdueQuery = $conn->query(
    "SELECT COUNT(*) AS total
     FROM maintenance_schedules
     WHERE status = 'pending'
     AND scheduled_date < CURDATE()"
);

$overdueData = $overdueQuery->fetch_assoc();

$overdueMaintenance = (int)$overdueData["total"];


/*
|--------------------------------------------------------------------------
| CRITICAL INVENTORY LIST
|--------------------------------------------------------------------------
*/

$criticalItemsQuery = $conn->query(
    "SELECT *
     FROM inventory
     WHERE quantity <= critical_level
     ORDER BY quantity ASC, item_name ASC
     LIMIT 5"
);


/*
|--------------------------------------------------------------------------
| UPCOMING MAINTENANCE LIST
|--------------------------------------------------------------------------
*/

$upcomingMaintenanceQuery = $conn->query(
    "SELECT *
     FROM maintenance_schedules
     WHERE status = 'pending'
     AND scheduled_date >= CURDATE()
     ORDER BY scheduled_date ASC, scheduled_time ASC
     LIMIT 5"
);

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

            <a
                href="dashboard.php"
                class="active"
            >
                🏠 Dashboard
            </a>


            <a href="inventory/index.php">
                📦 Inventory
            </a>


            <a href="maintenance/index.php">
                🔧 Maintenance
            </a>


            <a href="history/index.php">
                📋 History
            </a>

            <a href="../accounts/index.php">
                👤 Accounts
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


            <!-- TOTAL INVENTORY -->

            <div class="dashboard-card">

                <span>
                    Total Inventory
                </span>

                <strong>
                    <?= $totalInventory ?>
                </strong>

                <small>
                    Items being monitored
                </small>

            </div>


            <!-- CRITICAL STOCK -->

            <div class="dashboard-card">

                <span>
                    Critical Stock
                </span>

                <strong>
                    <?= $criticalStock ?>
                </strong>

                <small>
                    Items need attention
                </small>

            </div>


            <!-- UPCOMING MAINTENANCE -->

            <div class="dashboard-card">

                <span>
                    Upcoming Maintenance
                </span>

                <strong>
                    <?= $upcomingMaintenance ?>
                </strong>

                <small>
                    Scheduled tasks
                </small>

            </div>


            <!-- OVERDUE -->

            <div class="dashboard-card">

                <span>
                    Overdue
                </span>

                <strong>
                    <?= $overdueMaintenance ?>
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


                <?php if ($criticalItemsQuery->num_rows > 0): ?>


                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>
                                        Item
                                    </th>

                                    <th>
                                        Stock
                                    </th>

                                    <th>
                                        Critical Level
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php while ($item = $criticalItemsQuery->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($item["item_name"]) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= number_format((float)$item["quantity"], 2) ?>
                                        <?= htmlspecialchars($item["unit"]) ?>
                                    </td>

                                    <td>
                                        <?= number_format((float)$item["critical_level"], 2) ?>
                                    </td>

                                    <td>

                                        <span class="status critical">
                                            ⚠ Critical
                                        </span>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="empty-state">

                        <p>
                            ✓ No critical inventory items.
                        </p>

                    </div>


                <?php endif; ?>

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


                <?php if ($upcomingMaintenanceQuery->num_rows > 0): ?>


                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>
                                        Maintenance
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                    <th>
                                        Time
                                    </th>

                                    <th>
                                        Assigned To
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php while ($schedule = $upcomingMaintenanceQuery->fetch_assoc()): ?>

                                <tr>

                                    <td>

                                        <strong>
                                            <?= htmlspecialchars($schedule["title"]) ?>
                                        </strong>

                                    </td>

                                    <td>

                                        <?= date(
                                            "M d, Y",
                                            strtotime($schedule["scheduled_date"])
                                        ) ?>

                                    </td>

                                    <td>

                                        <?= date(
                                            "h:i A",
                                            strtotime($schedule["scheduled_time"])
                                        ) ?>

                                    </td>

                                    <td>

                                        <?= htmlspecialchars(
                                            $schedule["assigned_to"] ?: "Not assigned"
                                        ) ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="empty-state">

                        <p>
                            ✓ No upcoming maintenance.
                        </p>

                    </div>


                <?php endif; ?>

            </div>


        </section>


    </main>

</div>

</body>

</html>