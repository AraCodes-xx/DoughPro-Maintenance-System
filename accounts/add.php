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

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $role = $_POST["role"] ?? "maintenance";


    // VALIDATION

    if ($full_name === "" || $username === "" || $password === "") {

        $error = "Please complete all required fields.";

    } elseif (strlen($username) < 3) {

        $error = "Username must be at least 3 characters.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } elseif (!in_array($role, ["admin", "maintenance"])) {

        $error = "Invalid account role.";

    } else {

        // CHECK USERNAME

        $check = $conn->prepare("
            SELECT id
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        $check->bind_param("s", $username);
        $check->execute();

        $existing = $check->get_result();

        if ($existing->num_rows > 0) {

            $error = "Username already exists.";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare("
                INSERT INTO users
                (
                    username,
                    password,
                    full_name,
                    role
                )
                VALUES (?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "ssss",
                $username,
                $hashedPassword,
                $full_name,
                $role
            );

            if ($stmt->execute()) {

                header("Location: index.php");
                exit;

            } else {

                $error = "Failed to create account.";

            }

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

    <title>Add Account - DoughPro</title>

    <link rel="stylesheet" href="../css/style.css">

</head>


<body>

<div class="app">

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

                <strong>
                    <?= htmlspecialchars($_SESSION["full_name"]) ?>
                </strong>

                <span>Administrator</span>

            </div>

            <a
                href="../logout.php"
                class="logout"
                onclick="return confirm('Are you sure you want to logout?');"
            >
                ↪ Logout
            </a>

        </div>

    </aside>


    <main class="main-content">

        <div class="page-header">

            <div>

                <h1>Add Account</h1>

                <p>
                    Create a new DoughPro system account.
                </p>

            </div>

            <a href="index.php" class="back-button">
                ← Back to Accounts
            </a>

        </div>


        <div class="form-card">

            <?php if ($error): ?>

                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <div class="form-group">

                    <label for="full_name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        placeholder="e.g. Juan Dela Cruz"
                        required
                        value="<?= htmlspecialchars($_POST["full_name"] ?? "") ?>"
                    >

                </div>


                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter username"
                        required
                        value="<?= htmlspecialchars($_POST["username"] ?? "") ?>"
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Minimum 6 characters"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="confirm_password">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Re-enter password"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="role">
                        Account Role
                    </label>

                    <select id="role" name="role" required>

                        <option value="maintenance"
                            <?= (($_POST["role"] ?? "") === "maintenance") ? "selected" : "" ?>>
                            Maintenance Staff
                        </option>

                        <option value="admin"
                            <?= (($_POST["role"] ?? "") === "admin") ? "selected" : "" ?>>
                            Administrator
                        </option>

                    </select>

                </div>


                <div class="form-actions">

                    <a href="index.php" class="cancel-button">
                        Cancel
                    </a>

                    <button type="submit" class="save-button">
                        Create Account
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

</body>

</html>