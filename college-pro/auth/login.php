<?php
/**
 * User Login Page
 * Handles user authentication for both customers and admins
 */

require_once '../config/db.php';

$error = '';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: /college-pro/admin/dashboard.php');
    } else {
        header('Location: /college-pro/index.php');
    }
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        // Connect to database
        $conn = getDBConnection();
        
        // Get user from database
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if user is active
            if ($user['status'] !== 'active') {
                $error = 'Your account is inactive. Please contact support.';
            } elseif (password_verify($password, $user['password'])) {
                // Login successful - set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Handle remember me
                if ($remember_me) {
                    // Set cookie for 30 days
                    setcookie('remember_me', $user['id'], time() + (30 * 24 * 60 * 60), '/');
                }
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: /college-pro/admin/dashboard.php');
                } else {
                    header('Location: /college-pro/index.php');
                }
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bella Italia</title>
    <link rel="stylesheet" href="/college-pro/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-box">
                <h1>Bella Italia</h1>
                <h2>Login</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember_me" id="remember_me">
                            Remember me
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
                
                <p class="auth-link">
                    Don't have an account? <a href="/college-pro/auth/register.php">Register here</a>
                </p>
                
                <p class="auth-link">
                    <a href="/college-pro/admin/dashboard.php">Admin Login</a> | 
                    <a href="/college-pro/index.php">← Back to Home</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>

