<?php
// database connection 
function connectDB() {
    // Adjust path if needed; matches your lab example.
    $config = parse_ini_file("db1.ini");

    if ($config === false) {
        die("Error: Could not read db1.ini");
    }

    try {
        $dbh = new PDO($config['dsn'], $config['username'], $config['password']);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    } catch (PDOException $e) {
        print "DB Connection Error: " . $e->getMessage() . "<br/>";
        die();
    }
}

// Customer login
function authenticateCustomer($user, $passwd) {
    try {
        $dbh = connectDB();
        $stmt = $dbh->prepare("
            SELECT COUNT(*)
            FROM Customers
            WHERE user_name = :username
              AND password  = SHA2(:passwd, 256)
        ");
        $stmt->bindParam(":username", $user);
        $stmt->bindParam(":passwd",  $passwd);
        $stmt->execute();
        $row = $stmt->fetch();
        $dbh = null;
        return $row[0];
    } catch (PDOException $e) {
        print "Error!" . $e->getMessage() . "<br/>";
        die();
    }
}

// Employee login
function authenticateEmployee($user, $passwd) {
    try {
        $dbh = connectDB();
        $stmt = $dbh->prepare("
            SELECT employee_id, user_name, password, new_account
            FROM Employees
            WHERE user_name = :username
              AND password  = SHA2(:passwd, 256)
        ");
        $stmt->bindParam(":username", $user);
        $stmt->bindParam(":passwd",  $passwd);
        $stmt->execute();
        $row = $stmt->fetch();
        $dbh = null;
        return $row;
    } catch (PDOException $e) {
        print "Error!" . $e->getMessage() . "<br/>";
        die();
    }
}
?>
