<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $scheduled_date = $_POST["scheduled_date"] ?? "";
    $scheduled_time = $_POST["scheduled_time"] ?? "";
    $recurrence = $_POST["recurrence"] ?? "none";
    $assigned_to = trim($_POST["assigned_to"] ?? "");

    if (
        $title === "" ||
        $scheduled_date === "" ||
        $scheduled_time === ""
    ) {

        $error = "Please fill in all required fields.";

    } else {

        $sql = "INSERT INTO maintenance_schedules
                (
                    title,
                    description,
                    scheduled_date,
                    scheduled_time,
                    recurrence,
                    assigned_to,
                    status,
                    created_by
                )
                VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "ssssssi",
                $title,
                $description,
                $scheduled_date,
                $scheduled_time,
                $recurrence,
                $assigned_to,
                $_SESSION["user_id"]
            );

            if ($stmt->execute()) {

                header("Location: index.php");
                exit();

            } else {

                $error = "Failed to add maintenance schedule.";

            }

            $stmt->close();
        }
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

    <title>Add Maintenance | DoughPro</title>

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


    <!-- MAIN CONTENT -->

    <main class="main-content">

        <div class="topbar">

            <div>

                <h1>
                    Add Maintenance
                </h1>

                <p>
                    Create a new maintenance schedule.
                </p>

            </div>

        </div>


        <!-- FORM -->

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
                        placeholder="Example: Oven Cleaning"
                        value="<?= htmlspecialchars($_POST["title"] ?? "") ?>"
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
                        placeholder="Enter maintenance details..."
                    ><?= htmlspecialchars($_POST["description"] ?? "") ?></textarea>

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
                        value="<?= htmlspecialchars($_POST["scheduled_date"] ?? "") ?>"
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
                        value="<?= htmlspecialchars($_POST["scheduled_time"] ?? "") ?>"
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
                            <?= (($_POST["recurrence"] ?? "") === "none") ? "selected" : "" ?>
                        >
                            None
                        </option>

                        <option
                            value="daily"
                            <?= (($_POST["recurrence"] ?? "") === "daily") ? "selected" : "" ?>
                        >
                            Daily
                        </option>

                        <option
                            value="weekly"
                            <?= (($_POST["recurrence"] ?? "") === "weekly") ? "selected" : "" ?>
                        >
                            Weekly
                        </option>

                        <option
                            value="monthly"
                            <?= (($_POST["recurrence"] ?? "") === "monthly") ? "selected" : "" ?>
                        >
                            Monthly
                        </option>

                        <option
                            value="yearly"
                            <?= (($_POST["recurrence"] ?? "") === "yearly") ? "selected" : "" ?>
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
                        placeholder="Example: Maintenance Team"
                        value="<?= htmlspecialchars($_POST["assigned_to"] ?? "") ?>"
                    >

                </div>


                <!-- BUTTONS -->

                <div class="action-buttons">

                    <button
                        type="submit"
                        class="primary-button"
                    >
                        Save Maintenance
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