<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT * FROM inventory ORDER BY item_name ASC";
$result = $conn->query($sql);

if (!$result) {
    die("Database query error: " . $conn->error);
}

/* Summary */

$totalItems = $result->num_rows;
$criticalItems = 0;
$totalQuantity = 0;

$result->data_seek(0);

while ($row = $result->fetch_assoc()) {

    $totalQuantity += $row["quantity"];

    if ($row["quantity"] <= $row["critical_level"]) {
        $criticalItems++;
    }
}

$result->data_seek(0);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Inventory | DoughPro</title>

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

            <span>
                Maintenance Management
            </span>

        </div>

        <nav>

            <!-- DASHBOARD -->
            <a href="../dashboard.php">
                🏠 Dashboard
            </a>

            <!-- INVENTORY -->
            <a href="index.php" class="active">
                📦 Inventory
            </a>

            <!-- MAINTENANCE -->
            <a href="../maintenance/index.php">
                🔧 Maintenance
            </a>

            <!-- HISTORY -->
            <a href="../history/index.php">
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

        <div class="topbar">

            <div>

                <h1>
                    Inventory
                </h1>

                <p>
                    Monitor and manage DoughPro supplies.
                </p>

            </div>

            <a
                href="add.php"
                class="primary-button"
            >
                + Add Item
            </a>

        </div>


        <!-- SUMMARY CARDS -->

        <div class="dashboard-cards">

            <div class="dashboard-card">

                <span>
                    Total Items
                </span>

                <strong>
                    <?= $totalItems ?>
                </strong>

                <small>
                    Inventory items
                </small>

            </div>


            <div class="dashboard-card">

                <span>
                    Critical Stock
                </span>

                <strong>
                    <?= $criticalItems ?>
                </strong>

                <small>
                    Need attention
                </small>

            </div>


            <div class="dashboard-card">

                <span>
                    Total Quantity
                </span>

                <strong>
                    <?= number_format($totalQuantity, 2) ?>
                </strong>

                <small>
                    Across all items
                </small>

            </div>


            <div class="dashboard-card">

                <span>
                    System Status
                </span>

                <strong>
                    Active
                </strong>

                <small>
                    Inventory monitoring
                </small>

            </div>

        </div>


        <!-- SEARCH -->

        <div class="inventory-toolbar">

            <input
                type="text"
                id="searchInput"
                placeholder="Search inventory..."
            >

        </div>


        <!-- INVENTORY TABLE -->

        <div class="table-container">

            <table id="inventoryTable">

                <thead>

                    <tr>

                        <th>Item</th>

                        <th>Quantity</th>

                        <th>Unit</th>

                        <th>Critical Level</th>

                        <th>Status</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($result->num_rows > 0): ?>

                    <?php while ($item = $result->fetch_assoc()): ?>

                        <?php

                        $isCritical =
                            $item["quantity"] <=
                            $item["critical_level"];

                        ?>

                        <tr>

                            <td>

                                <strong>
                                    <?= htmlspecialchars(
                                        $item["item_name"]
                                    ) ?>
                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $item["quantity"]
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $item["unit"]
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $item["critical_level"]
                                ) ?>

                            </td>


                            <td>

                                <?php if ($isCritical): ?>

                                    <span class="status critical">
                                        ⚠ Critical
                                    </span>

                                <?php else: ?>

                                    <span class="status normal">
                                        ✓ Normal
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <div class="action-buttons">

                                    <a
                                        href="stock.php?id=<?= $item["id"] ?>"
                                        class="stock-button"
                                    >
                                        Stock
                                    </a>

                                    <a
                                        href="edit.php?id=<?= $item["id"] ?>"
                                        class="edit-button"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="delete.php?id=<?= $item["id"] ?>"
                                        class="delete-button"
                                        onclick="return confirm('Delete this inventory item?');"
                                    >
                                        Delete
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="6"
                            class="empty-table"
                        >
                            No inventory items found.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </main>

</div>


<!-- SEARCH SCRIPT -->

<script>

const searchInput =
    document.getElementById("searchInput");

const rows =
    document.querySelectorAll(
        "#inventoryTable tbody tr"
    );

searchInput.addEventListener(
    "keyup",
    function () {

        const searchValue =
            this.value.toLowerCase();

        rows.forEach(function (row) {

            const text =
                row.textContent.toLowerCase();

            if (text.includes(searchValue)) {

                row.style.display = "";

            } else {

                row.style.display = "none";

            }

        });

    }
);

</script>

</body>

</html>