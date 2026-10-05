<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $itemName = trim($_POST["item_name"] ?? "");
    $quantity = $_POST["quantity"] ?? "";
    $unit = trim($_POST["unit"] ?? "");
    $criticalLevel = $_POST["critical_level"] ?? "";

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
        | Check if Item Already Exists
        |--------------------------------------------------------------------------
        */

        $check = $conn->prepare(
            "SELECT *
             FROM inventory
             WHERE item_name = ?
             LIMIT 1"
        );

        $check->bind_param("s", $itemName);
        $check->execute();

        $result = $check->get_result();

        /*
        |--------------------------------------------------------------------------
        | EXISTING ITEM
        |--------------------------------------------------------------------------
        */

        if ($result->num_rows === 1) {

            $existingItem = $result->fetch_assoc();

            $inventoryId = (int)$existingItem["id"];
            $previousQuantity = (float)$existingItem["quantity"];
            $newQuantity = $previousQuantity + (float)$quantity;

            /*
            | Update existing stock
            */

            $update = $conn->prepare(
                "UPDATE inventory
                 SET quantity = ?,
                     unit = ?,
                     critical_level = ?
                 WHERE id = ?"
            );

            $update->bind_param(
                "dsdi",
                $newQuantity,
                $unit,
                $criticalLevel,
                $inventoryId
            );

            if ($update->execute()) {

                /*
                | Record as Stock In
                */

                $history = $conn->prepare(
                    "INSERT INTO inventory_history
                    (
                        inventory_id,
                        action,
                        quantity,
                        previous_quantity,
                        new_quantity,
                        notes,
                        performed_by
                    )
                    VALUES (?, 'stock_in', ?, ?, ?, ?, ?)"
                );

                $notes = "Added through Add Item.";

                $history->bind_param(
                    "idddsi",
                    $inventoryId,
                    $quantity,
                    $previousQuantity,
                    $newQuantity,
                    $notes,
                    $_SESSION["user_id"]
                );

                $history->execute();
                $history->close();

                $update->close();
                $check->close();

                header("Location: index.php");
                exit();

            } else {

                $error = "Unable to update existing inventory item.";

            }

            $update->close();

        /*
        |--------------------------------------------------------------------------
        | NEW ITEM
        |--------------------------------------------------------------------------
        */

        } else {

            $sql = "INSERT INTO inventory
                    (
                        item_name,
                        quantity,
                        unit,
                        critical_level
                    )
                    VALUES (?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "sdsd",
                $itemName,
                $quantity,
                $unit,
                $criticalLevel
            );

            if ($stmt->execute()) {

                $stmt->close();
                $check->close();

                header("Location: index.php");
                exit();

            } else {

                $error = "Unable to add inventory item.";

            }

            $stmt->close();
        }

        $check->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Inventory | DoughPro</title>

<link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="app">

<aside class="sidebar">

<div class="logo">

<h2>DoughPro</h2>

<span>Maintenance Management</span>

</div>

<nav>

<a href="../dashboard.php">🏠 Dashboard</a>

<a href="index.php" class="active">📦 Inventory</a>

<a href="../maintenance/index.php">🔧 Maintenance</a>

<a href="../history/index.php">📋 History</a>

</nav>

<div class="sidebar-bottom">

<div class="admin-info">

<strong>
<?= htmlspecialchars($_SESSION["full_name"]) ?>
</strong>

<small>Administrator</small>

</div>

<a href="../logout.php" class="logout">
🚪 Logout
</a>

</div>

</aside>


<main class="main-content">

<div class="topbar">

<div>

<h1>Add Inventory Item</h1>

<p>
Add a new item or add stock to an existing item.
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
placeholder="e.g. Flour"
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
placeholder="e.g. 20"
required
>

<small>
If the item already exists, this quantity will be added to its current stock.
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
placeholder="e.g. sacks, bars, packs"
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
placeholder="e.g. 5"
required
>

<small>
The system will mark the item as Critical when quantity reaches this level.
</small>

</div>


<div class="action-buttons">

<button
type="submit"
class="primary-button"
>
Save Item
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