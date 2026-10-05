<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Get Maintenance Schedules
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM maintenance_schedules
        ORDER BY scheduled_date ASC, scheduled_time ASC";

$result = $conn->query($sql);

if (!$result) {
    die("Database query error: " . $conn->error);
}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalSchedules = 0;
$pendingSchedules = 0;
$completedSchedules = 0;
$overdueSchedules = 0;

$schedules = [];

while ($row = $result->fetch_assoc()) {

    $today = date("Y-m-d");

    /*
     * Automatically display overdue if:
     * - status is pending
     * - scheduled date is before today
     */
    if (
        $row["status"] === "pending" &&
        $row["scheduled_date"] < $today
    ) {
        $displayStatus = "overdue";
    } else {
        $displayStatus = $row["status"];
    }

    $row["display_status"] = $displayStatus;

    $schedules[] = $row;

    $totalSchedules++;

    if ($displayStatus === "pending") {
        $pendingSchedules++;
    }

    if ($displayStatus === "completed") {
        $completedSchedules++;
    }

    if ($displayStatus === "overdue") {
        $overdueSchedules++;
    }
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

    <title>Maintenance | DoughPro</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>


<body>

<div class="app">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside class="sidebar">

        <div class="logo">

            <h2>DoughPro</h2>

            <span>
                Maintenance Management
            </span>

        </div>


        <nav>

            <a href="../dashboard.php">
                🏠 Dashboard
            </a>


            <a href="../inventory/index.php">
                📦 Inventory
            </a>


            <a
                href="index.php"
                class="active"
            >
                🔧 Maintenance
            </a>


            <a href="../history/index.php">
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
                href="../logout.php"
                class="logout"
            >
                🚪 Logout
            </a>

        </div>

    </aside>



    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="main-content">


        <!-- TOPBAR -->

        <div class="topbar">

            <div>

                <h1>
                    Maintenance
                </h1>

                <p>
                    Monitor and manage maintenance schedules.
                </p>

            </div>


            <a
                href="add.php"
                class="primary-button"
            >
                + Add Maintenance
            </a>

        </div>



        <!-- =====================================================
             SUMMARY CARDS
        ====================================================== -->

        <div class="dashboard-cards">


            <div class="dashboard-card">

                <span>
                    Total Schedules
                </span>

                <strong>
                    <?= $totalSchedules ?>
                </strong>

                <small>
                    Maintenance tasks
                </small>

            </div>


            <div class="dashboard-card">

                <span>
                    Pending
                </span>

                <strong>
                    <?= $pendingSchedules ?>
                </strong>

                <small>
                    Upcoming tasks
                </small>

            </div>


            <div class="dashboard-card">

                <span>
                    Completed
                </span>

                <strong>
                    <?= $completedSchedules ?>
                </strong>

                <small>
                    Finished tasks
                </small>

            </div>


            <div class="dashboard-card">

                <span>
                    Overdue
                </span>

                <strong>
                    <?= $overdueSchedules ?>
                </strong>

                <small>
                    Need attention
                </small>

            </div>

        </div>



        <!-- =====================================================
             SEARCH
        ====================================================== -->

        <div class="inventory-toolbar">

            <input
                type="text"
                id="searchInput"
                placeholder="Search maintenance schedule..."
            >

        </div>



        <!-- =====================================================
             MAINTENANCE TABLE
        ====================================================== -->

        <div class="table-container">

            <table id="maintenanceTable">

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
                            Recurrence
                        </th>

                        <th>
                            Assigned To
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (count($schedules) > 0): ?>


                    <?php foreach ($schedules as $schedule): ?>


                        <tr>


                            <!-- TITLE -->

                            <td>

                                <strong>
                                    <?= htmlspecialchars(
                                        $schedule["title"]
                                    ) ?>
                                </strong>


                                <?php if (!empty($schedule["description"])): ?>

                                    <br>

                                    <small>
                                        <?= htmlspecialchars(
                                            $schedule["description"]
                                        ) ?>
                                    </small>

                                <?php endif; ?>

                            </td>



                            <!-- DATE -->

                            <td>

                                <?= date(
                                    "M d, Y",
                                    strtotime($schedule["scheduled_date"])
                                ) ?>

                            </td>



                            <!-- TIME -->

                            <td>

                                <?= date(
                                    "h:i A",
                                    strtotime($schedule["scheduled_time"])
                                ) ?>

                            </td>



                            <!-- RECURRENCE -->

                            <td>

                                <?php

                                $recurrence =
                                    ucfirst(
                                        $schedule["recurrence"]
                                    );

                                ?>

                                <?= htmlspecialchars($recurrence) ?>

                            </td>



                            <!-- ASSIGNED TO -->

                            <td>

                                <?php if (!empty($schedule["assigned_to"])): ?>

                                    <?= htmlspecialchars(
                                        $schedule["assigned_to"]
                                    ) ?>

                                <?php else: ?>

                                    <span>
                                        Not assigned
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- STATUS -->

                            <td>

                                <?php if ($schedule["display_status"] === "completed"): ?>

                                    <span class="status normal">
                                        ✓ Completed
                                    </span>


                                <?php elseif ($schedule["display_status"] === "overdue"): ?>

                                    <span class="status critical">
                                        ⚠ Overdue
                                    </span>


                                <?php elseif ($schedule["display_status"] === "cancelled"): ?>

                                    <span class="status critical">
                                        ✕ Cancelled
                                    </span>


                                <?php else: ?>

                                    <span class="status normal">
                                        ● Pending
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- ACTIONS -->

                            <td>

                                <div class="action-buttons">

                                    <?php if ($schedule["display_status"] !== "completed"): ?>

                                        <a
                                            href="complete.php?id=<?= $schedule["id"] ?>"
                                            class="stock-button"
                                            onclick="return confirm('Mark this maintenance as completed?');"
                                        >
                                            ✓ Complete
                                        </a>

                                    <?php endif; ?>


                                    <a
                                        href="edit.php?id=<?= $schedule["id"] ?>"
                                        class="edit-button"
                                    >
                                        Edit
                                    </a>


                                    <a
                                        href="delete.php?id=<?= $schedule["id"] ?>"
                                        class="delete-button"
                                        onclick="return confirm('Delete this maintenance schedule?');"
                                    >
                                        Delete
                                    </a>

                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="7"
                            class="empty-table"
                        >
                            No maintenance schedules found.
                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>


    </main>

</div>



<!-- =====================================================
     SEARCH SCRIPT
====================================================== -->

<script>

const searchInput =
    document.getElementById("searchInput");

const rows =
    document.querySelectorAll(
        "#maintenanceTable tbody tr"
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