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
| Check if Item Exists
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT id
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

$stmt->close();


/*
|--------------------------------------------------------------------------
| Delete Item
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "DELETE FROM inventory
     WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Return to Inventory
|--------------------------------------------------------------------------
*/

header("Location: index.php");
exit();

?>