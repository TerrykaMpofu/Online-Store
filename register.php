<?php
session_start();
require 'db1.php';

$error = null;

if (isset($_POST['register'])) {
    $first    = trim($_POST['first_name']  ?? '');
    $last     = trim($_POST['last_name']   ?? '');
    $username = trim($_POST['user_name']   ?? '');   // matches DB column user_name
    $email    = trim($_POST['email']       ?? '');
    $password = $_POST['password']         ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $address  = trim($_POST['address']     ?? '');

    // Basic validation
    if ($first === '' || $last === '' || $username === '' || $email === '' || $address === '') {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            $dbh = connectDB();

            // Check if username or email already exists
            $stmt = $dbh->prepare("
                SELECT customer_id
                FROM Customers
                WHERE user_name = :username OR email = :email
            ");
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email",    $email);
            $stmt->execute();

            if ($stmt->fetch()) {
                $error = "Username or email already exists.";
            } else {
                // Insert new customer; user_name column matches variable $username
                $stmt = $dbh->prepare("
                    INSERT INTO Customers (user_name, first_name, last_name, email, password, address)
                    VALUES (:username, :first, :last, :email, SHA2(:passwd,256), :address)
                ");
                $stmt->bindParam(":username", $username);
                $stmt->bindParam(":first",    $first);
                $stmt->bindParam(":last",     $last);
                $stmt->bindParam(":email",    $email);
                $stmt->bindParam(":passwd",   $password);
                $stmt->bindParam(":address",  $address);
                $stmt->execute();

                $dbh = null;
                // After successful registration, go to customer login
                header("Location: cust_login.php?action=login");
                exit;
            }

            $dbh = null;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<body>
<h2>Register New Customer</h2>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" action="register.php">
    <label>First Name:</label><br>
    <input type="text" name="first_name" required><br>

    <label>Last Name:</label><br>
    <input type="text" name="last_name" required><br>

    <label>Username:</label><br>
    <!-- name="user_name" matches DB column user_name -->
    <input type="text" name="user_name" required><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br>

    <label>Confirm Password:</label><br>
    <input type="password" name="confirm_password" required><br>

    <label>Address:</label><br>
    <input type="text" name="address" required><br><br>

    <input type="submit" name="register" value="Register">
</form>

<p><a href="cust_login.php?action=login">Back to Login</a></p>
</body>
</html>
