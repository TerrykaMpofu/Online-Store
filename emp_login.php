<?php
session_start();
require 'db1.php';

$dbh    = connectDB();
$action = $_GET['action'] ?? 'login';
$error  = null;

// Logout
if ($action === 'logout') {
    session_unset();
    session_destroy();
    header("Location: emp_login.php");
    exit;
}

// Process login form 
if ($action === 'do_login' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Look up this employee
    $stmt = $dbh->prepare("
        Select employee_id, user_name, password, new_account
        FROM Employees
        WHERE user_name = :username
          And password  = SHA2(:passwd, 256)
    ");
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":passwd",  $password);
    $stmt->execute();
    $emp = $stmt->fetch();

    if ($emp) {
        // Save basic info in session
        $_SESSION['employee_id']   = $emp['employee_id'];
        $_SESSION['employee_name'] = $emp['user_name'];
        $_SESSION['new_account']   = (bool)$emp['new_account'];

        if ($emp['new_account']) {
            // First login: must change password
            $action = 'change_password';
        } else {
            header("Location: emp_main.php"); // go to employee main page
            exit;
        }
    } else {
        $error  = "Incorrect username or password.";
        $action = 'login';
    }
}

// Process first-time password change 
if ($action === 'do_change_password' && isset($_POST['change'])) {
    if (!isset($_SESSION['employee_id'])) {
        header("Location: emp_login.php");
        exit;
    }

    $new_pass = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (strlen($new_pass) < 6) {
        $error  = "Password must be at least 6 characters.";
        $action = 'change_password';
    } elseif ($new_pass !== $confirm) {
        $error  = "Passwords do not match.";
        $action = 'change_password';
    } else {
        // Update password 
        $stmt = $dbh->prepare("
            UPDATE Employees
            SET password = SHA2(:passwd,256), new_account = 0
            WHERE employee_id = :id
        ");
        $stmt->bindParam(":passwd", $new_pass);
        $stmt->bindParam(":id",     $_SESSION['employee_id']);
        $stmt->execute();

        $_SESSION['new_account'] = false;
        header("Location: emp_main.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<body>

<?php if ($action === 'login'): ?>

    <h2>Employee Login</h2>
    <?php if ($error): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" action="emp_login.php?action=do_login">
        <label>Username:</label><br>
        <input type="text" name="username" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <input type="submit" name="login" value="Login">
    </form>

    <p><a href="cust_main.php">Back to Store</a></p>

<?php elseif ($action === 'change_password'): ?>

    <h2>First Login - Change Password</h2>
    <?php if ($error): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" action="emp_login.php?action=do_change_password">
        <label>New Password:</label><br>
        <input type="password" name="new_password" required><br>
        <label>Confirm Password:</label><br>
        <input type="password" name="confirm_password" required><br><br>
        <input type="submit" name="change" value="Change Password">
    </form>

    <p><a href="emp_login.php?action=logout">Cancel and Logout</a></p>

<?php endif; ?>

</body>
</html>
