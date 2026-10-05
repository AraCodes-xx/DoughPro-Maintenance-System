<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

require_once "../config/database.php";

$result = $conn->query("
    SELECT id, username, full_name, role, created_at
    FROM users
    ORDER BY
        CASE WHEN role = 'admin' THEN 0 ELSE 1 END,
        full_name ASC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Accounts - DoughPro</title>

   <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="sidebar-header">
            <h2>DoughPro</h2>
            <p>Maintenance System</p>
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

            <a href="../history/index.php">
                📜 History
            </a>

            <a href="index.php" class="active">
                👤 Accounts
            </a>

        </nav>

        <div class="sidebar-bottom">

            <div class="user-info">
                <strong><?= htmlspecialchars($_SESSION["full_name"]) ?></strong>
                <span>Administrator</span>
            </div>

            <a href="../logout.php" class="logout"
               onclick="return confirm('Are you sure you want to logout?');">
                ↪ Logout
            </a>

        </div>

    </aside>


    <!-- MAIN CONTENT -->
    <main class="main-content">

        <div class="page-header">

            <div>
                <h1>Manage Accounts</h1>
                <p>Manage DoughPro system users and maintenance staff accounts.</p>
            </div>

            <a href="add.php" class="add-button">
                + Add Account
            </a>

        </div>


        <!-- ACCOUNT CARDS -->

        <div class="summary-grid">

            <?php

            $adminCount = 0;
            $maintenanceCount = 0;

            $countResult = $conn->query("
                SELECT role, COUNT(*) AS total
                FROM users
                GROUP BY role
            ");

            while ($count = $countResult->fetch_assoc()) {

                if ($count["role"] === "admin") {
                    $adminCount = $count["total"];
                }

                if ($count["role"] === "maintenance") {
                    $maintenanceCount = $count["total"];
                }
            }

            ?>

            <div class="summary-card">
                <span class="summary-label">Total Accounts</span>
                <strong><?= $adminCount + $maintenanceCount ?></strong>
            </div>

            <div class="summary-card">
                <span class="summary-label">Administrators</span>
                <strong><?= $adminCount ?></strong>
            </div>

            <div class="summary-card">
                <span class="summary-label">Maintenance Staff</span>
                <strong><?= $maintenanceCount ?></strong>
            </div>

        </div>


        <!-- ACCOUNTS TABLE -->

        <div class="table-card">

            <div class="table-header">

                <div>
                    <h2>System Accounts</h2>
                    <p>Registered DoughPro users</p>
                </div>

                <input
                    type="text"
                    id="accountSearch"
                    placeholder="Search accounts..."
                    class="search-input"
                >

            </div>


            <div class="table-wrapper">

                <table id="accountsTable">

                    <thead>

                        <tr>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($result->num_rows > 0): ?>

                        <?php while ($account = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($account["full_name"]) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($account["username"]) ?>
                                </td>

                                <td>

                                    <?php if ($account["role"] === "admin"): ?>

                                        <span class="status-badge status-good">
                                            Administrator
                                        </span>

                                    <?php else: ?>

                                        <span class="status-badge status-warning">
                                            Maintenance Staff
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= date("M d, Y", strtotime($account["created_at"])) ?>
                                </td>

                                <td>

                                    <a
                                        href="edit.php?id=<?= $account["id"] ?>"
                                        class="edit-button"
                                    >
                                        Edit
                                    </a>


                                    <?php if ($account["id"] != $_SESSION["user_id"]): ?>

                                        <a
                                            href="delete.php?id=<?= $account["id"] ?>"
                                            class="delete-button"
                                            onclick="return confirm('Delete this account? This action cannot be undone.');"
                                        >
                                            Delete
                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Current Account
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="5" class="empty-state">
                                No accounts found.
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>


<script>

const searchInput = document.getElementById("accountSearch");
const table = document.getElementById("accountsTable");

searchInput.addEventListener("input", function () {

    const search = this.value.toLowerCase();

    const rows = table.querySelectorAll("tbody tr");

    rows.forEach(function (row) {

        const text = row.textContent.toLowerCase();

        row.style.display =
            text.includes(search) ? "" : "none";

    });

});

</script>

</body>
</html>