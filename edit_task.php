<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$task_id = isset($_GET["id"]) ? $_GET["id"] : 0;
$user_id = $_SESSION["user_id"];
$task_text = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_id = $_POST["id"];
    $task_text = trim($_POST["task"]);
    
    if (empty($task_text)) {
        $_SESSION["error"] = "Task cannot be empty";
        header("Location: edit_task.php?id=$task_id");
        exit;
    }
    
    $sql = "UPDATE tasks SET task = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $task_text, $task_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION["success"] = "Task updated successfully";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION["error"] = "Error updating task: " . $conn->error;
    }
    
    $stmt->close();
}

$sql = "SELECT task FROM tasks WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION["error"] = "Task not found or you don't have permission to edit it";
    header("Location: index.php");
    exit;
}

$row = $result->fetch_assoc();
$task_text = $row["task"];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container app-container">
        <header class="app-header">
            <h1 class="app-title">To-Do List</h1>
            <div class="user-section">
                <span class="user-welcome">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>
        
        <form action="index.php" method="post" class="task-form">
            <input type="text" name="task" class="task-input" placeholder="What's your new task?" required>
            <button type="submit" name="add_task" class="add-btn">Add Task</button>
        </form>
        
        <div class="lists-container">
            <section class="task-section">
                <h2 class="section-title">Task Lists</h2>
                
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="task-item">
                            <span class="task-text"><?php echo $row["task_id"] . ". " . htmlspecialchars($row["task_text"]); ?></span>
                            <div class="task-actions">
                                <a href="edit_task.php?id=<?php echo $row["id"]; ?>" class="btn edit-btn">Edit</a>
                                <a href="index.php?complete=<?php echo $row["id"]; ?>" class="btn complete-btn">Complete</a>
                                <a href="index.php?delete=<?php echo $row["id"]; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this task?')">Delete</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <div class="pagination">
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="index.php?page=<?php echo $i; ?>" class="page-link <?php echo ($current_page == $i) ? 'current-page' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if($current_page < $total_pages): ?>
                            <a href="index.php?page=<?php echo $current_page + 1; ?>" class="page-link">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p>No tasks yet. Add your first task above!</p>
                <?php endif; ?>
            </section>
            
            <section class="task-section">
                <h2 class="section-title">Completed Tasks</h2>
                
                <?php if ($completed_result->num_rows > 0): ?>
                    <?php while($row = $completed_result->fetch_assoc()): ?>
                        <div class="task-item">
                            <span class="task-text"><?php echo htmlspecialchars($row["task_text"]); ?></span>
                            <div class="task-actions">
                                <a href="index.php?delete_completed=<?php echo $row["id"]; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this completed task?')">Delete</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No completed tasks yet.</p>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>
</html>