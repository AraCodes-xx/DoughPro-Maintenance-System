<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Get Inventory Item
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT *
     FROM inventory
     WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();

    header("Location: index.php");
    exit();
}

$item = $result->fetch_assoc();

$stmt->close();


$error = "";


/*
|--------------------------------------------------------------------------
| Update Inventory
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $itemName = trim($_POST["item_name"] ?? "");
    $quantity = $_POST["quantity"] ?? "";
    $unit = trim($_POST["unit"] ?? "");
    $criticalLevel = $_POST["critical_level"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $itemName === "" ||
        $quantity === "" ||
        $unit === "" ||
        $criticalLevel === ""
    ) {

        $error = "Please complete all fields.";

    } elseif (
        !is_numeric($quantity) ||
        !is_numeric($criticalLevel)
    ) {

        $error = "Quantity and critical level must be numbers.";

    } elseif (
        $quantity < 0 ||
        $criticalLevel < 0
    ) {

        $error = "Quantity and critical level cannot be negative.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $sql = "UPDATE inventory
                SET
                    item_name = ?,
                    quantity = ?,
                    unit = ?,
                    critical_level = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "sdsdi",
            $itemName,
            $quantity,
            $unit,
            $criticalLevel,
            $id
        );


        if ($stmt->execute()) {

            $stmt->close();

            header("Location: index.php");
            exit();

        } else {

            $error = "Unable to update inventory item.";

        }

        $stmt->close();
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

    <title>Edit Inventory | DoughPro</title>

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

            <a href="index.php" class="active">
                📦 Inventory
            </a>

            <a href="../maintenance/index.php">
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
                    Edit Inventory Item
                </h1>

                <p>
                    Update inventory information.
                </p>

            </div>

        </div>



        <section class="dashboard-panel">


            <?php if ($error !== ""): ?>

                <div class="status critical">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <div class="form-group">

                    <label for="item_name">
                        Item Name
                    </label>

                    <input
                        type="text"
                        id="item_name"
                        name="item_name"
                        value="<?= htmlspecialchars($item["item_name"]) ?>"
                        required
                    >

                </div>



                <div class="form-group">

                    <label for="quantity">
                        Quantity
                    </label>

                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        min="0"
                        step="0.01"
                        value="<?= htmlspecialchars($item["quantity"]) ?>"
                        required
                    >

                    <small>
                        Use Stock for normal Stock In or Stock Out
                        transactions.
                    </small>

                </div>



                <div class="form-group">

                    <label for="unit">
                        Unit
                    </label>

                    <input
                        type="text"
                        id="unit"
                        name="unit"
                        value="<?= htmlspecialchars($item["unit"]) ?>"
                        required
                    >

                </div>



                <div class="form-group">

                    <label for="critical_level">
                        Critical Level
                    </label>

                    <input
                        type="number"
                        id="critical_level"
                        name="critical_level"
                        min="0"
                        step="0.01"
                        value="<?= htmlspecialchars($item["critical_level"]) ?>"
                        required
                    >

                    <small>
                        The item becomes Critical when its
                        quantity reaches this level.
                    </small>

                </div>



                <div class="action-buttons">

                    <button
                        type="submit"
                        class="primary-button"
                    >
                        Save Changes
                    </button>


                    <a
                        href="index.php"
                        class="edit-button"
                    >
                        Cancel
                    </a>

                </div>


            </form>


        </section>


    </main>

</div>

</body>

</html>