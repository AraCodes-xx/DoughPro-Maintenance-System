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
| Check if Maintenance Exists
|--------------------------------------------------------------------------
*/

$sql = "SELECT id FROM maintenance_schedules WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();

    header("Location: index.php");
    exit();
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| Delete Maintenance
|--------------------------------------------------------------------------
*/

$sql = "DELETE FROM maintenance_schedules WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Return to Maintenance
|--------------------------------------------------------------------------
*/

header("Location: index.php");
exit();

?>