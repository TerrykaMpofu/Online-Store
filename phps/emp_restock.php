<?php
// restock product by employee
require 'common.php';   
require_employee_login();

$dbh     = connectDB();
$error   = null;
$success = null;

// Load products for dropdown
$stmt = $dbh->query("
    SELECT product_id, name, stock_qty
    FROM Products
    ORDER BY name
");
$products = $stmt->fetchAll();

// Handle form submit
if (isset($_POST['restock'])) {
    $product_id   = (int)$_POST['product_id'];
    $add_quantity = (int)$_POST['quantity'];

    if ($add_quantity <= 0) {
        $error = "Quantity must be greater than 0.";
    } else {
        try {
            $dbh->beginTransaction();

            // Lock row and get current stock + price
            $stmt = $dbh->prepare("
                SELECT stock_qty, price
                FROM Products
                WHERE product_id = :pid
                FOR UPDATE
            ");
            $stmt->bindParam(":pid", $product_id);
            $stmt->execute();
            $row = $stmt->fetch();

            if (!$row) {
                throw new Exception("Product not found.");
            }

            $old_qty = (int)$row['stock_qty'];
            $new_qty = $old_qty + $add_quantity;
            $price   = $row['price'];

            // Update Products table
            $stmt = $dbh->prepare("
                UPDATE Products
                SET stock_qty = :new_qty,
                    last_modified = NOW(),
                    last_modified_by = :emp
                WHERE product_id = :pid
            ");
            $stmt->bindParam(":new_qty", $new_qty);
            $stmt->bindParam(":emp", $_SESSION['employee_id']);
            $stmt->bindParam(":pid", $product_id);
            $stmt->execute();

            // Insert into Product_history for stock change
            $stmt = $dbh->prepare("
                INSERT INTO Product_history
                    (action, product_id, time, employee_id,
                     old_price, new_price, old_quantity, new_quantity)
                VALUES
                    ('UPDATE', :pid, NOW(), :emp,
                     :old_price, :new_price, :old_qty, :new_qty)
            ");
            $stmt->bindParam(":pid",       $product_id);
            $stmt->bindParam(":emp",       $_SESSION['employee_id']);
            $stmt->bindParam(":old_price", $price);
            $stmt->bindParam(":new_price", $price);   // price unchanged
            $stmt->bindParam(":old_qty",   $old_qty);
            $stmt->bindParam(":new_qty",   $new_qty);
            $stmt->execute();

            $dbh->commit();
            $success = "Restocked successfully. New quantity: $new_qty.";
        } catch (Exception $e) {
            $dbh->rollBack();
            $error = "Restock failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<body>
<h2>Restock Product</h2>

<p><a href="emp_main.php">Back to Employee Main</a></p>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post" action="emp_restock.php">
    <label>Select Product:</label><br>
    <select name="product_id" required>
        <option value="">-- Choose --</option>
        <?php foreach ($products as $p): ?>
            <option value="<?= $p['product_id'] ?>">
                <?= htmlspecialchars($p['name']) ?> (current: <?= (int)$p['stock_qty'] ?>)
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Quantity to add:</label><br>
    <input type="number" name="quantity" min="1" required><br><br>

    <input type="submit" name="restock" value="Restock">
</form>
</body>
</html>