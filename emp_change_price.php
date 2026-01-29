<?php
// Employee change product price
require 'common.php';     
require_employee_login();

$dbh     = connectDB();
$error   = null;
$success = null;

// Load products for dropdown
$stmt = $dbh->query("
    SELECT product_id, name, price
    FROM Products
    ORDER BY name
");
$products = $stmt->fetchAll();

// Handle form submit
if (isset($_POST['change_price'])) {
    $product_id = (int)$_POST['product_id'];
    $new_price  = (float)$_POST['new_price'];

    if ($new_price <= 0) {
        $error = "Price must be greater than 0.";
    } else {
        try {
            $dbh->beginTransaction();

            // Get current price and stock for history
            $stmt = $dbh->prepare("
                SELECT price, stock_qty
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

            $old_price = (float)$row['price'];
            $stock_qty = (int)$row['stock_qty'];

            // Update Products.price
            $stmt = $dbh->prepare("
                UPDATE Products
                SET price = :new_price,
                    last_modified = NOW(),
                    last_modified_by = :emp
                WHERE product_id = :pid
            ");
            $stmt->bindParam(":new_price", $new_price);
            $stmt->bindParam(":emp",       $_SESSION['employee_id']);
            $stmt->bindParam(":pid",       $product_id);
            $stmt->execute();

            // Insert into Product_history for price change
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
            $stmt->bindParam(":old_price", $old_price);
            $stmt->bindParam(":new_price", $new_price);
            $stmt->bindParam(":old_qty",   $stock_qty);
            $stmt->bindParam(":new_qty",   $stock_qty);  // quantity unchanged
            $stmt->execute();

            $dbh->commit();
            $success = "Price updated from $$old_price to $$new_price.";
        } catch (Exception $e) {
            $dbh->rollBack();
            $error = "Price change failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<body>
<h2>Change Product Price</h2>

<p><a href="emp_main.php">Back to Employee Main</a></p>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post" action="emp_change_price.php">
    <label>Select Product:</label><br>
    <select name="product_id" required>
        <option value="">-- Choose --</option>
        <?php foreach ($products as $p): ?>
            <option value="<?= $p['product_id'] ?>">
                <?= htmlspecialchars($p['name']) ?> (current: $<?= number_format($p['price'], 2) ?>)
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>New Price:</label><br>
    <input type="number" name="new_price" step="0.01" min="0.01" required><br><br>

    <input type="submit" name="change_price" value="Update Price">
</form>
</body>
</html>
