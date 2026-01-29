<?php
// Customer Login
session_start();
require 'db1.php';

$error = null;

// If user clicked logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: cust_main.php");  // back to main browsing page
    exit;
}

// If user submitted the login form
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Check username + password using helper in db1.php
    if (authenticateCustomer($username, $password) == 1) {

        // Get customer_id for the session
        $dbh = connectDB();
        $stmt = $dbh->prepare("
            SELECT customer_id
            FROM Customers
            WHERE user_name = :username
        ");
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $row = $stmt->fetch();
        $dbh = null;

        // Save login info in session
        $_SESSION['customer_id'] = $row['customer_id'];
        $_SESSION['username']    = $username;

        // Go to customer main page
        header("Location: cust_main.php");
        exit;

    } else {
        $error = "Incorrect username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<body>
<h2>Customer Login</h2>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" action="login1.php">
    <label>Username:</label><br>
    <input type="text" name="username" required><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <input type="submit" name="login" value="Login">
</form>

<p>
    <a href="register.php">Register</a> |
    <a href="cust_main.php">Back to Store</a>
</p>
</body>
</html>
