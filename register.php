<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

require_once "db.php";

$username = $password = $confirm_password = "";
$username_error = $password_error = $confirm_password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_error = "Please enter a username";
    } else {
        // Check if username already exists
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $param_username);
        $param_username = trim($_POST["username"]);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $username_error = "This username is already taken";
        } else {
            $username = trim($_POST["username"]);
        }
        
        $stmt->close();
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_error = "Please enter a password";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_error = "Password must have at least 6 characters";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_error = "Please confirm password";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if ($password != $confirm_password) {
            $confirm_password_error = "Passwords do not match";
        }
    }
    
    // Check for errors before creating user
    if (empty($username_error) && empty($password_error) && empty($confirm_password_error)) {
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $param_username, $param_password);
        
        $param_username = $username;
        $param_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password
        
        if ($stmt->execute()) {
            // Success - redirect to login
            $_SESSION["success"] = "Account created successfully. Please log in.";
            header("Location: login.php");
            exit;
        } else {
            $register_error = "Something went wrong. Please try again later.";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - To-Do List</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container auth-container">
        <h1>Create a New Account</h1>
        
        <?php if (isset($register_error)): ?>
            <div class="alert alert-error"><?php echo $register_error; ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="post" class="auth-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo $username; ?>" required>
                <?php if (!empty($username_error)): ?>
                    <span class="error"><?php echo $username_error; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <?php if (!empty($password_error)): ?>
                    <span class="error"><?php echo $password_error; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <?php if (!empty($confirm_password_error)): ?>
                    <span class="error"><?php echo $confirm_password_error; ?></span>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>