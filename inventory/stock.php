<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

$id = (int)($_GET["id"] ?? 0);

$stmt = $conn->prepare("SELECT * FROM inventory WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Inventory item not found.");
}

$item = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>DoughPro | Stock Management</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

<div class="app">

    <aside class="sidebar">

        <div class="logo">
            <h2>DoughPro</h2>
            <span>Maintenance System</span>
        </div>

        <nav>

            <a href="../dashboard.php">
                Dashboard
            </a>

            <a href="index.php" class="active">
                Inventory
            </a>

            <a href="../maintenance/">
                Maintenance
            </a>

            <a href="../history/">
                History
            </a>

        </nav>

        <div class="sidebar-bottom">

            <div class="admin-info">

                <strong>
                    <?= htmlspecialchars($_SESSION["full_name"]) ?>
                </strong>

                <small>Administrator</small>

            </div>

            <a href="../logout.php" class="logout">
                Logout
            </a>

        </div>

    </aside>


    <main class="main-content">

        <header class="topbar">

            <div>

                <h1>Stock Management</h1>

                <p>
                    Update inventory quantity.
                </p>

            </div>

            <a
                href="index.php"
                class="secondary-button"
            >
                ← Back to Inventory
            </a>

        </header>


        <div class="stock-layout">

            <!-- CURRENT STOCK -->

            <div class="dashboard-panel">

                <span class="stock-label">
                    Current Inventory
                </span>

                <h2>
                    <?= htmlspecialchars($item["item_name"]) ?>
                </h2>

                <div class="current-stock">

                    <?= htmlspecialchars($item["quantity"]) ?>

                    <span>
                        <?= htmlspecialchars($item["unit"]) ?>
                    </span>

                </div>

                <p>
                    Critical level:
                    <strong>
                        <?= htmlspecialchars($item["critical_level"]) ?>
                        <?= htmlspecialchars($item["unit"]) ?>
                    </strong>
                </p>

            </div>


            <!-- STOCK FORM -->

            <div class="dashboard-panel">

                <h2>Record Stock Movement</h2>

                <form
                    action="process_stock.php"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="inventory_id"
                        value="<?= $item["id"] ?>"
                    >


                    <div class="form-group">

                        <label>
                            Action
                        </label>

                        <select
                            name="action"
                            required
                        >

                            <option value="stock_in">
                                📥 Stock In
                            </option>

                            <option value="stock_out">
                                📤 Stock Out
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            Quantity
                        </label>

                        <input
                            type="number"
                            name="quantity"
                            min="0.01"
                            step="0.01"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Notes
                        </label>

                        <textarea
                            name="notes"
                            placeholder="Example: Used for today's production"
                            rows="4"
                        ></textarea>

                    </div>


                    <button
                        type="submit"
                        class="primary-button full-width"
                    >
                        Save Stock Movement
                    </button>

                </form>

            </div>

        </div>

    </main>

</div>

</body>

</html>
