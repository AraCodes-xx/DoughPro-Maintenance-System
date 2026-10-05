<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Get Maintenance Schedule
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM maintenance_schedules WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: index.php");
    exit();
}

$schedule = $result->fetch_assoc();
$stmt->close();


/*
|--------------------------------------------------------------------------
| Start Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {

    /*
    |--------------------------------------------------------------------------
    | Save to Maintenance History
    |--------------------------------------------------------------------------
    */

    $historySql = "INSERT INTO maintenance_history
        (
            maintenance_id,
            title,
            scheduled_date,
            scheduled_time,
            action,
            notes,
            performed_by
        )
        VALUES (?, ?, ?, ?, 'completed', ?, ?)";

    $historyStmt = $conn->prepare($historySql);

    $notes = "Maintenance completed.";

    $historyStmt->bind_param(
        "issssi",
        $id,
        $schedule["title"],
        $schedule["scheduled_date"],
        $schedule["scheduled_time"],
        $notes,
        $_SESSION["user_id"]
    );

    $historyStmt->execute();
    $historyStmt->close();


    /*
    |--------------------------------------------------------------------------
    | Update Maintenance Status
    |--------------------------------------------------------------------------
    */

    $today = date("Y-m-d");

    $updateSql = "UPDATE maintenance_schedules
                  SET
                      status = 'completed',
                      last_completed = ?
                  WHERE id = ?";

    $updateStmt = $conn->prepare($updateSql);

    $updateStmt->bind_param(
        "si",
        $today,
        $id
    );

    $updateStmt->execute();
    $updateStmt->close();


    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $conn->commit();

} catch (Exception $e) {

    $conn->rollback();

    die("Unable to complete maintenance: " . $e->getMessage());
}


/*
|--------------------------------------------------------------------------
| Return to Maintenance
|--------------------------------------------------------------------------
*/

header("Location: index.php");
exit();

?>