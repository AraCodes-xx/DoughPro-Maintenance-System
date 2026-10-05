<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$inventory_id = (int)($_POST["inventory_id"] ?? 0);

$action = $_POST["action"] ?? "";

$quantity = (float)($_POST["quantity"] ?? 0);

$notes = trim($_POST["notes"] ?? "");

$user_id = $_SESSION["user_id"];


if (
    $inventory_id <= 0 ||
    !in_array($action, ["stock_in", "stock_out"]) ||
    $quantity <= 0
) {
    die("Invalid stock movement.");
}


/*
|--------------------------------------------------------------------------
| Get Current Inventory
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT *
     FROM inventory
     WHERE id = ?
     FOR UPDATE"
);

$conn->begin_transaction();

try {

    $stmt->bind_param("i", $inventory_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {

        throw new Exception("Inventory item not found.");

    }

    $item = $result->fetch_assoc();

    $previous_quantity =
        (float)$item["quantity"];


    /*
    |--------------------------------------------------------------------------
    | Calculate New Quantity
    |--------------------------------------------------------------------------
    */

    if ($action === "stock_in") {

        $new_quantity =
            $previous_quantity + $quantity;

    } else {

        $new_quantity =
            $previous_quantity - $quantity;

        /*
        | Prevent negative inventory
        */

        if ($new_quantity < 0) {

            throw new Exception(
                "Stock Out cannot make inventory negative."
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Update Inventory
    |--------------------------------------------------------------------------
    */

    $update = $conn->prepare(
        "UPDATE inventory
         SET quantity = ?
         WHERE id = ?"
    );

    $update->bind_param(
        "di",
        $new_quantity,
        $inventory_id
    );

    $update->execute();

    $update->close();


    /*
    |--------------------------------------------------------------------------
    | Save History
    |--------------------------------------------------------------------------
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
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $history->bind_param(
        "isdddsi",
        $inventory_id,
        $action,
        $quantity,
        $previous_quantity,
        $new_quantity,
        $notes,
        $user_id
    );

    $history->execute();

    $history->close();


    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    header("Location: index.php");
    exit;


} catch (Exception $e) {

    $conn->rollback();

    die(
        "Stock movement failed: "
        . htmlspecialchars($e->getMessage())
    );

}

?>