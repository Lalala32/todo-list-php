<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task = trim($_POST["task"]);
    $user_id = $_SESSION["user_id"];
    
    if (empty($task)) {
        $_SESSION["error"] = "Task cannot be empty";
        header("Location: index.php");
        exit;
    }
    
    $sql = "INSERT INTO tasks (user_id, task) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $task);
    
    if ($stmt->execute()) {
        $_SESSION["success"] = "Task added successfully";
    } else {
        $_SESSION["error"] = "Error adding task: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: index.php");
    exit;
}
?>