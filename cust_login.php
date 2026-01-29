<?php
require 'common.php';   // start session

$dbh    = connectDB();
$action = $_GET['action'] ?? 'login';
$error  = null;
$success = null;

// Logout
if ($action === 'logout') {
    session_unset();
    session_destroy();
    header("Location: cust_main.php");
    exit;
}

// Process login
if ($action === 'do_login' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error  = "Username and password are required.";
        $action = 'login';
    } else {
        if (authenticateCustomer($username, $password) == 1) {
            // fetch customer_id for session
            $stmt = $dbh->prepare("
                SELECT customer_id
                FROM Customers
                WHERE user_name = :username
            ");
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            $row = $stmt->fetch();

            $_SESSION['customer_id'] = $row['customer_id'];
            $_SESSION['username']    = $username;

            header("Location: cust_main.php");
            exit;
        } else {
            $error  = "Incorrect username or password.";
            $action = 'login';
        }
    }
}

// Process password change
if ($action === 'do_change' && isset($_POST['change'])) {
    // must be logged in
    if (!isset($_SESSION['customer_id'])) {
        header("Location: cust_login.php");
        exit;
    }

    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($new_pass !== $confirm) {
        $error  = "New passwords do not match.";
        $action = 'change_password';
    } elseif (strlen($new_pass) < 6) {
        $error  = "New password must be at least 6 characters.";
        $action = 'change_password';
    } else {
        // verify old password
        $stmt = $dbh->prepare("
            SELECT customer_id
            FROM Customers
            WHERE customer_id = :id
              AND password    = SHA2(:old, 256)
        ");
        $stmt->bindParam(":id",  $_SESSION['customer_id']);
        $stmt->bindParam(":old", $old_pass);
        $stmt->execute();
        $row = $stmt->fetch();

        if (!$row) {
            $error  = "Old password is incorrect.";
            $action = 'change_password';
        } else {
            // update password
            $stmt = $dbh->prepare("
                UPDATE Customers
                SET password = SHA2(:new, 256)
                WHERE customer_id = :id
            ");
            $stmt->bindParam(":new", $new_pass);
            $stmt->bindParam(":id",  $_SESSION['customer_id']);
            $stmt->execute();

            $success = "Password changed successfully.";
            $action  = 'change_password';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<body>

<p>
    <a href="cust_login.php?action=login">Customer Login</a> |
    <?php if (isset($_SESSION['customer_id'])): ?>
        <a href="cust_login.php?action=change_password">Change Password</a> |
        <a href="cust_login.php?action=logout">Logout</a> |
    <?php endif; ?>
    <a href="register.php">Register</a> |
    <a href="cust_main.php">Customer Main</a>
</p>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php if ($action === 'login'): ?>

    <!-- LOGIN FORM -->
    <h2>Customer Login</h2>
    <form method="post" action="cust_login.php?action=do_login">
        <label>Username:</label><br>
        <input type="text" name="username" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <input type="submit" name="login" value="Login">
    </form>

<?php elseif ($action === 'change_password' && isset($_SESSION['customer_id'])): ?>

    <!-- CHANGE PASSWORD FORM -->
    <h2>Change Password</h2>
    <form method="post" action="cust_login.php?action=do_change">
        <label>Old Password:</label><br>
        <input type="password" name="old_password" required><br>
        <label>New Password:</label><br>
        <input type="password" name="new_password" required><br>
        <label>Confirm New Password:</label><br>
        <input type="password" name="confirm_password" required><br><br>
        <input type="submit" name="change" value="Change Password">
    </form>

<?php endif; ?>

</body>
</html>
