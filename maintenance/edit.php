<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Get Maintenance ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Get Existing Maintenance
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM maintenance_schedules WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();

    die("Maintenance schedule not found.");
}

$schedule = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Update Maintenance
|--------------------------------------------------------------------------
*/

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $scheduled_date = $_POST["scheduled_date"] ?? "";
    $scheduled_time = $_POST["scheduled_time"] ?? "";
    $recurrence = $_POST["recurrence"] ?? "none";
    $assigned_to = trim($_POST["assigned_to"] ?? "");
    $status = $_POST["status"] ?? "pending";


    /*
    |--------------------------------------------------------------------------
    | Validate
    |--------------------------------------------------------------------------
    */

    $allowedStatuses = [
        "pending",
        "completed",
        "overdue",
        "cancelled"
    ];

    $allowedRecurrence = [
        "none",
        "daily",
        "weekly",
        "monthly",
        "yearly"
    ];


    if (
        $title === "" ||
        $scheduled_date === "" ||
        $scheduled_time === ""
    ) {

        $error = "Please fill in all required fields.";

    } elseif (!in_array($recurrence, $allowedRecurrence, true)) {

        $error = "Invalid recurrence selected.";

    } elseif (!in_array($status, $allowedStatuses, true)) {

        $error = "Invalid status selected.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Update Database
        |--------------------------------------------------------------------------
        */

        $sql = "UPDATE maintenance_schedules
                SET
                    title = ?,
                    description = ?,
                    scheduled_date = ?,
                    scheduled_time = ?,
                    recurrence = ?,
                    assigned_to = ?,
                    status = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "sssssssi",
                $title,
                $description,
                $scheduled_date,
                $scheduled_time,
                $recurrence,
                $assigned_to,
                $status,
                $id
            );


            if ($stmt->execute()) {

                $stmt->close();

                header("Location: index.php");
                exit();

            } else {

                $error = "Failed to update maintenance schedule.";

                $stmt->close();
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Keep Submitted Values
    |--------------------------------------------------------------------------
    */

    $schedule["title"] = $title;
    $schedule["description"] = $description;
    $schedule["scheduled_date"] = $scheduled_date;
    $schedule["scheduled_time"] = $scheduled_time;
    $schedule["recurrence"] = $recurrence;
    $schedule["assigned_to"] = $assigned_to;
    $schedule["status"] = $status;
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

    <title>Edit Maintenance | DoughPro</title>

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


        <div class="topbar">

            <div>

                <h1>
                    Edit Maintenance
                </h1>

                <p>
                    Update the maintenance schedule.
                </p>

            </div>

        </div>



        <!-- =====================================================
             FORM
        ====================================================== -->

        <div class="dashboard-panel">


            <?php if ($error !== ""): ?>

                <div class="status critical">
                    <?= htmlspecialchars($error) ?>
                </div>

                <br>

            <?php endif; ?>


            <form method="POST">


                <!-- TITLE -->

                <div class="form-group">

                    <label for="title">
                        Maintenance Title *
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($schedule["title"]) ?>"
                        required
                    >

                </div>



                <!-- DESCRIPTION -->

                <div class="form-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                    ><?= htmlspecialchars($schedule["description"] ?? "") ?></textarea>

                </div>



                <!-- DATE -->

                <div class="form-group">

                    <label for="scheduled_date">
                        Date *
                    </label>

                    <input
                        type="date"
                        id="scheduled_date"
                        name="scheduled_date"
                        value="<?= htmlspecialchars($schedule["scheduled_date"]) ?>"
                        required
                    >

                </div>



                <!-- TIME -->

                <div class="form-group">

                    <label for="scheduled_time">
                        Time *
                    </label>

                    <input
                        type="time"
                        id="scheduled_time"
                        name="scheduled_time"
                        value="<?= htmlspecialchars($schedule["scheduled_time"]) ?>"
                        required
                    >

                </div>



                <!-- RECURRENCE -->

                <div class="form-group">

                    <label for="recurrence">
                        Recurrence
                    </label>

                    <select
                        id="recurrence"
                        name="recurrence"
                    >

                        <option
                            value="none"
                            <?= $schedule["recurrence"] === "none" ? "selected" : "" ?>
                        >
                            None
                        </option>


                        <option
                            value="daily"
                            <?= $schedule["recurrence"] === "daily" ? "selected" : "" ?>
                        >
                            Daily
                        </option>


                        <option
                            value="weekly"
                            <?= $schedule["recurrence"] === "weekly" ? "selected" : "" ?>
                        >
                            Weekly
                        </option>


                        <option
                            value="monthly"
                            <?= $schedule["recurrence"] === "monthly" ? "selected" : "" ?>
                        >
                            Monthly
                        </option>


                        <option
                            value="yearly"
                            <?= $schedule["recurrence"] === "yearly" ? "selected" : "" ?>
                        >
                            Yearly
                        </option>

                    </select>

                </div>



                <!-- ASSIGNED TO -->

                <div class="form-group">

                    <label for="assigned_to">
                        Assigned To
                    </label>

                    <input
                        type="text"
                        id="assigned_to"
                        name="assigned_to"
                        value="<?= htmlspecialchars($schedule["assigned_to"] ?? "") ?>"
                        placeholder="Example: Maintenance Team"
                    >

                </div>



                <!-- STATUS -->

                <div class="form-group">

                    <label for="status">
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                    >

                        <option
                            value="pending"
                            <?= $schedule["status"] === "pending" ? "selected" : "" ?>
                        >
                            Pending
                        </option>


                        <option
                            value="completed"
                            <?= $schedule["status"] === "completed" ? "selected" : "" ?>
                        >
                            Completed
                        </option>


                        <option
                            value="overdue"
                            <?= $schedule["status"] === "overdue" ? "selected" : "" ?>
                        >
                            Overdue
                        </option>


                        <option
                            value="cancelled"
                            <?= $schedule["status"] === "cancelled" ? "selected" : "" ?>
                        >
                            Cancelled
                        </option>

                    </select>

                </div>



                <!-- BUTTONS -->

                <div class="action-buttons">

                    <button
                        type="submit"
                        class="primary-button"
                    >
                        Save Changes
                    </button>


                    <a
                        href="index.php"
                        class="secondary-button"
                    >
                        Cancel
                    </a>

                </div>


            </form>

        </div>


    </main>

</div>

</body>

</html>