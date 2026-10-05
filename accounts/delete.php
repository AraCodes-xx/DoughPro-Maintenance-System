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


// PREVENT SELF-DELETION

if ($id == $_SESSION["user_id"]) {

    header("Location: index.php");
    exit;

}


// CHECK IF ACCOUNT EXISTS

$stmt = $conn->prepare("
    SELECT id
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


// DELETE ACCOUNT

$delete = $conn->prepare("
    DELETE FROM users
    WHERE id = ?
");

$delete->bind_param("i", $id);

$delete->execute();


header("Location: index.php");
exit;

?>