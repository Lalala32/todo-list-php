<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$user_id = $_SESSION["user_id"];
$items_per_page = 5; // Tasks per page

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

$count_query = "SELECT COUNT(*) as total FROM tasks WHERE user_id = $user_id AND is_completed = 0";
$count_result = $conn->query($count_query);
$total_tasks = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_tasks / $items_per_page);

$sql = "SELECT * FROM tasks WHERE user_id = $user_id AND is_completed = 0 ORDER BY created_at DESC LIMIT $offset, $items_per_page";
$result = $conn->query($sql);


$completed_items_per_page = 5;
$completed_page = isset($_GET['completed_page']) ? (int)$_GET['completed_page'] : 1;
$completed_offset = ($completed_page - 1) * $completed_items_per_page;

$completed_count_query = "SELECT COUNT(*) as total FROM tasks WHERE user_id = $user_id AND is_completed = 1";
$completed_count_result = $conn->query($completed_count_query);
$total_completed_tasks = $completed_count_result->fetch_assoc()['total'];
$total_completed_pages = ceil($total_completed_tasks / $completed_items_per_page);

$completed_sql = "SELECT * FROM tasks WHERE user_id = $user_id AND is_completed = 1 
                  ORDER BY created_at DESC LIMIT $completed_offset, $completed_items_per_page";
$completed_result = $conn->query($completed_sql);
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
    <div class="container">
        <header>
            <h1>To-Do List</h1>
            <div class="user-info">
                <p>Welcome, <?php echo $_SESSION["username"]; ?></p>
                <a href="logout.php" class="btn btn-logout">Logout</a>
            </div>
        </header>

        <form action="add_task.php" method="post" class="task-form">
            <input type="text" name="task" placeholder="What's your new task?" required>
            <button type="submit" class="btn btn-add">Add Task</button>
        </form>

        <div class="content">
            <!-- Active Tasks with Index -->
            <div class="task-list">
                <h2>Task Lists</h2>
                <?php if ($result->num_rows > 0): ?>
                    <?php $index = $offset + 1; ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="task-item">
                            <span><?php echo $index . ". " . htmlspecialchars($row["task"]); ?></span>
                            <div class="task-actions">
                                <a href="edit_task.php?id=<?php echo $row["id"]; ?>" class="btn btn-edit">Edit</a>
                                <a href="complete_task.php?id=<?php echo $row["id"]; ?>" class="btn btn-complete">Complete</a>
                                <a href="delete_task.php?id=<?php echo $row["id"]; ?>" class="btn btn-delete">Delete</a>
                            </div>
                        </div>
                        <?php $index++; ?>
                    <?php endwhile; ?>

                    <!-- Pagination for Active Tasks -->
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page-1; ?>" class="btn">&laquo; Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="btn <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page+1; ?>" class="btn">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-message">No tasks yet. Add a new task to get started!</p>
                <?php endif; ?>
            </div>

            <!-- Completed Tasks with Index -->
            <div class="completed-tasks">
                <h2>Completed Tasks</h2>
                <?php if ($completed_result->num_rows > 0): ?>
                    <?php $completed_index = $completed_offset + 1; ?>
                    <?php while($row = $completed_result->fetch_assoc()): ?>
                        <div class="task-item completed">
                            <span><?php echo $completed_index . ". " . htmlspecialchars($row["task"]); ?></span>
                            <a href="delete_task.php?id=<?php echo $row["id"]; ?>" class="btn btn-delete">Delete</a>
                        </div>
                        <?php $completed_index++; ?>
                    <?php endwhile; ?>

                    <!-- Pagination for Completed Tasks -->
                    <div class="pagination">
                        <?php if ($completed_page > 1): ?>
                            <a href="?completed_page=<?php echo $completed_page-1; ?>" class="btn">&laquo; Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_completed_pages; $i++): ?>
                            <a href="?completed_page=<?php echo $i; ?>" class="btn <?php echo ($i == $completed_page) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($completed_page < $total_completed_pages): ?>
                            <a href="?completed_page=<?php echo $completed_page+1; ?>" class="btn">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-message">No completed tasks yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
