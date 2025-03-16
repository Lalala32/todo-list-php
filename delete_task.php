<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

if (isset($_GET["id"])) {
    $task_id = $_GET["id"];
    $user_id = $_SESSION["user_id"];
    
    $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $task_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION["success"] = "Task deleted successfully";
    } else {
        $_SESSION["error"] = "Error deleting task: " . $conn->error;
    }
    
    $stmt->close();
}

header("Location: index.php");
exit;
?>