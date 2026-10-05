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

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}


// GET ACCOUNT

$stmt = $conn->prepare("
    SELECT id, username, full_name, role
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$account = $result->fetch_assoc();

$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $role = $_POST["role"] ?? "";
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    if ($full_name === "" || $username === "") {

        $error = "Full name and username are required.";

    } elseif (strlen($username) < 3) {

        $error = "Username must be at least 3 characters.";

    } elseif (!in_array($role, ["admin", "maintenance"])) {

        $error = "Invalid account role.";

    } elseif ($password !== "" && strlen($password) < 6) {

        $error = "New password must be at least 6 characters.";

    } elseif ($password !== "" && $password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        // CHECK DUPLICATE USERNAME

        $check = $conn->prepare("
            SELECT id
            FROM users
            WHERE username = ?
            AND id != ?
            LIMIT 1
        ");

        $check->bind_param(
            "si",
            $username,
            $id
        );

        $check->execute();

        $duplicate = $check->get_result();


        if ($duplicate->num_rows > 0) {

            $error = "Username already exists.";

        } else {

            /*
             * Prevent the currently logged-in admin
             * from accidentally changing their own
             * account into a maintenance account.
             */

            if ($id == $_SESSION["user_id"] && $role !== "admin") {

                $error = "You cannot change your own account from Administrator to Maintenance Staff.";

            } else {

                if ($password !== "") {

                    $hashedPassword = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $update = $conn->prepare("
                        UPDATE users
                        SET
                            username = ?,
                            full_name = ?,
                            role = ?,
                            password = ?
                        WHERE id = ?
                    ");

                    $update->bind_param(
                        "ssssi",
                        $username,
                        $full_name,
                        $role,
                        $hashedPassword,
                        $id
                    );

                } else {

                    $update = $conn->prepare("
                        UPDATE users
                        SET
                            username = ?,
                            full_name = ?,
                            role = ?
                        WHERE id = ?
                    ");

                    $update->bind_param(
                        "sssi",
                        $username,
                        $full_name,
                        $role,
                        $id
                    );

                }


                if ($update->execute()) {

                    /*
                     * If the admin edited their own
                     * username/full name, update session.
                     */

                    if ($id == $_SESSION["user_id"]) {

                        $_SESSION["username"] = $username;
                        $_SESSION["full_name"] = $full_name;
                        $_SESSION["role"] = $role;

                    }

                    header("Location: index.php");
                    exit;

                } else {

                    $error = "Failed to update account.";

                }

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

    <title>Edit Account - DoughPro</title>

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

                <h1>Edit Account</h1>

                <p>
                    Update account information.
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
                        required
                        value="<?= htmlspecialchars($_POST["full_name"] ?? $account["full_name"]) ?>"
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
                        required
                        value="<?= htmlspecialchars($_POST["username"] ?? $account["username"]) ?>"
                    >

                </div>


                <div class="form-group">

                    <label for="role">
                        Account Role
                    </label>

                    <select
                        id="role"
                        name="role"
                        required
                    >

                        <option
                            value="maintenance"
                            <?= (($_POST["role"] ?? $account["role"]) === "maintenance") ? "selected" : "" ?>
                        >
                            Maintenance Staff
                        </option>

                        <option
                            value="admin"
                            <?= (($_POST["role"] ?? $account["role"]) === "admin") ? "selected" : "" ?>
                        >
                            Administrator
                        </option>

                    </select>

                </div>


                <div class="form-section-divider"></div>


                <h3>Change Password</h3>

                <p class="form-help">
                    Leave these fields blank if you do not want to change the password.
                </p>


                <div class="form-group">

                    <label for="password">
                        New Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Leave blank to keep current password"
                    >

                </div>


                <div class="form-group">

                    <label for="confirm_password">
                        Confirm New Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Re-enter new password"
                    >

                </div>


                <div class="form-actions">

                    <a
                        href="index.php"
                        class="cancel-button"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="save-button"
                    >
                        Save Changes
                    </button>

                </div>


            </form>

        </div>


    </main>

</div>

</body>

</html>