<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Inventory History
|--------------------------------------------------------------------------
*/

$inventorySql = "SELECT
                    ih.*,
                    i.item_name,
                    i.unit,
                    u.full_name
                FROM inventory_history ih
                LEFT JOIN inventory i
                    ON ih.inventory_id = i.id
                LEFT JOIN users u
                    ON ih.performed_by = u.id
                ORDER BY ih.created_at DESC";

$inventoryResult = $conn->query($inventorySql);


/*
|--------------------------------------------------------------------------
| Maintenance History
|--------------------------------------------------------------------------
*/

$maintenanceSql = "SELECT
                        mh.*,
                        u.full_name
                    FROM maintenance_history mh
                    LEFT JOIN users u
                        ON mh.performed_by = u.id
                    ORDER BY mh.created_at DESC";

$maintenanceResult = $conn->query($maintenanceSql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>DoughPro | History</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>


<body>

<div class="app">


    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="logo">

            <h2>DoughPro</h2>

            <span>Maintenance System</span>

        </div>


        <nav>

            <a href="../dashboard.php">
                🏠 Dashboard
            </a>

            <a href="../inventory/index.php">
                📦 Inventory
            </a>

            <a href="../maintenance/index.php">
                🔧 Maintenance
            </a>

            <a href="index.php" class="active">
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
                href="../logout.php"
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

                <h1>History</h1>

                <p>
                    View inventory and maintenance activity.
                </p>

            </div>

        </header>



        <!-- INVENTORY HISTORY -->

        <section class="dashboard-panel">

            <div class="panel-header">

                <div>

                    <h2>
                        📦 Inventory History
                    </h2>

                    <p>
                        Stock In and Stock Out records.
                    </p>

                </div>

            </div>


            <?php if ($inventoryResult && $inventoryResult->num_rows > 0): ?>

                <div class="table-container">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Item
                                </th>

                                <th>
                                    Action
                                </th>

                                <th>
                                    Quantity
                                </th>

                                <th>
                                    Previous
                                </th>

                                <th>
                                    New Stock
                                </th>

                                <th>
                                    Notes
                                </th>

                                <th>
                                    Performed By
                                </th>

                                <th>
                                    Date
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while ($history = $inventoryResult->fetch_assoc()): ?>


                            <tr>

                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $history["item_name"]
                                        ) ?>
                                    </strong>

                                </td>


                                <td>

                                    <?php if ($history["action"] === "stock_in"): ?>

                                        <span class="status completed">
                                            ↑ Stock In
                                        </span>

                                    <?php elseif ($history["action"] === "stock_out"): ?>

                                        <span class="status overdue">
                                            ↓ Stock Out
                                        </span>

                                    <?php else: ?>

                                        <span class="status">
                                            Adjustment
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?= rtrim(
                                        rtrim(
                                            number_format(
                                                $history["quantity"],
                                                2
                                            ),
                                            "0"
                                        ),
                                        "."
                                    ) ?>

                                    <?= htmlspecialchars(
                                        $history["unit"]
                                    ) ?>

                                </td>


                                <td>

                                    <?= rtrim(
                                        rtrim(
                                            number_format(
                                                $history["previous_quantity"],
                                                2
                                            ),
                                            "0"
                                        ),
                                        "."
                                    ) ?>

                                </td>


                                <td>

                                    <strong>
                                        <?= rtrim(
                                            rtrim(
                                                number_format(
                                                    $history["new_quantity"],
                                                    2
                                                ),
                                                "0"
                                            ),
                                            "."
                                        ) ?>
                                    </strong>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $history["notes"] ?? "-"
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $history["full_name"] ?? "Unknown"
                                    ) ?>

                                </td>


                                <td>

                                    <?= date(
                                        "M d, Y h:i A",
                                        strtotime(
                                            $history["created_at"]
                                        )
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
                        No inventory history yet.
                    </p>

                </div>

            <?php endif; ?>

        </section>



        <!-- MAINTENANCE HISTORY -->

        <section class="dashboard-panel">

            <div class="panel-header">

                <div>

                    <h2>
                        🔧 Maintenance History
                    </h2>

                    <p>
                        Records of completed maintenance activities.
                    </p>

                </div>

            </div>


            <?php if ($maintenanceResult && $maintenanceResult->num_rows > 0): ?>

                <div class="table-container">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Maintenance
                                </th>

                                <th>
                                    Scheduled Date
                                </th>

                                <th>
                                    Time
                                </th>

                                <th>
                                    Action
                                </th>

                                <th>
                                    Performed By
                                </th>

                                <th>
                                    Date Recorded
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while ($history = $maintenanceResult->fetch_assoc()): ?>


                            <tr>

                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $history["title"]
                                        ) ?>
                                    </strong>

                                </td>


                                <td>

                                    <?= date(
                                        "M d, Y",
                                        strtotime(
                                            $history["scheduled_date"]
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <?= date(
                                        "h:i A",
                                        strtotime(
                                            $history["scheduled_time"]
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <span class="status completed">
                                        ✓ Completed
                                    </span>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $history["full_name"] ?? "Unknown"
                                    ) ?>

                                </td>


                                <td>

                                    <?= date(
                                        "M d, Y h:i A",
                                        strtotime(
                                            $history["created_at"]
                                        )
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
                        No maintenance history yet.
                    </p>

                </div>

            <?php endif; ?>

        </section>


    </main>

</div>

</body>

</html>