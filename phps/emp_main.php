<?php
require 'common.php';   // session start + connectDB 
require_employee_login();

$dbh    = connectDB();
$action = $_GET['action'] ?? 'menu';
$error  = null;
$success = null;
?>
<!DOCTYPE html>
<html>
<body>

<h2>Employee Page</h2>
<p>Welcome, <?= htmlspecialchars($_SESSION['employee_name']) ?></p>

<p>
    <a href="emp_main.php?action=menu">Main Menu</a> |
    <a href="emp_main.php?action=restock">Restock Product</a> |
    <a href="emp_main.php?action=change_price">Change Price</a> |
    <a href="emp_main.php?action=stock_history">Stock History</a> |
    <a href="emp_main.php?action=price_history">Price History</a> |
    <a href="emp_login.php?action=logout">Logout</a>
</p>

<?php
// MAIN MENU 
if ($action === 'menu'): ?>
    <p>Select a function above.</p>

<?php
// RESTOCK PRODUCT 
elseif ($action === 'restock'):

    // Load products
    $stmt = $dbh->query("SELECT product_id, name, stock_qty FROM Products ORDER BY name");
    $products = $stmt->fetchAll();

    if (isset($_POST['restock'])) {
        $product_id   = (int)$_POST['product_id'];
        $add_quantity = (int)$_POST['quantity'];

        if ($add_quantity <= 0) {
            $error = "Quantity must be greater than 0.";
        } else {
            try {
                $dbh->beginTransaction();

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
                $stmt->bindParam(":new_price", $price);
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

    if ($error)   echo "<p style='color:red'>".htmlspecialchars($error)."</p>";
    if ($success) echo "<p style='color:green'>".htmlspecialchars($success)."</p>";
    ?>

    <h3>Restock Product</h3>
    <form method="post" action="emp_main.php?action=restock">
        <label>Product:</label><br>
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

<?php
// CHANGE PRODUCT PRICE
elseif ($action === 'change_price'):

    $stmt = $dbh->query("SELECT product_id, name, price FROM Products ORDER BY name");
    $products = $stmt->fetchAll();

    if (isset($_POST['change_price'])) {
        $product_id = (int)$_POST['product_id'];
        $new_price  = (float)$_POST['new_price'];

        if ($new_price <= 0) {
            $error = "Price must be greater than 0.";
        } else {
            try {
                $dbh->beginTransaction();

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
                $stmt->bindParam(":new_qty",   $stock_qty);
                $stmt->execute();

                $dbh->commit();
                $success = "Price updated from $$old_price to $$new_price.";
            } catch (Exception $e) {
                $dbh->rollBack();
                $error = "Price change failed: " . $e->getMessage();
            }
        }
    }

    if ($error)   echo "<p style='color:red'>".htmlspecialchars($error)."</p>";
    if ($success) echo "<p style='color:green'>".htmlspecialchars($success)."</p>";
    ?>

    <h3>Change Product Price</h3>
    <form method="post" action="emp_main.php?action=change_price">
        <label>Product:</label><br>
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

<?php
// Stock history
elseif ($action === 'stock_history'):

    $stmt = $dbh->query("SELECT product_id, name FROM Products ORDER BY name");
    $products = $stmt->fetchAll();
    $selected_product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $history = [];

    if ($selected_product_id > 0) {
        $stmt = $dbh->prepare("
            SELECT time, action, old_quantity, new_quantity, old_price, new_price, employee_id
            FROM Product_history
            WHERE product_id = :pid
            ORDER BY time DESC
        ");
        $stmt->bindParam(":pid", $selected_product_id);
        $stmt->execute();
        $history = $stmt->fetchAll();
    }
    ?>

    <h3>Stock History</h3>
    <form method="post" action="emp_main.php?action=stock_history">
        <label>Product:</label><br>
        <select name="product_id" required>
            <option value="">-- Choose --</option>
            <?php foreach ($products as $p): ?>
                <option value="<?= $p['product_id'] ?>"
                    <?= ($selected_product_id == $p['product_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="View History">
    </form>

    <?php if ($selected_product_id && $history): ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>Time</th>
                <th>Action</th>
                <th>Old Qty</th>
                <th>New Qty</th>
                <th>Employee ID</th>
            </tr>
            <?php foreach ($history as $h): ?>
                <tr>
                    <td><?= htmlspecialchars($h['time']) ?></td>
                    <td><?= htmlspecialchars($h['action']) ?></td>
                    <td><?= htmlspecialchars($h['old_quantity']) ?></td>
                    <td><?= htmlspecialchars($h['new_quantity']) ?></td>
                    <td><?= htmlspecialchars($h['employee_id']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($selected_product_id): ?>
        <p>No history records for this product.</p>
    <?php endif; ?>

<?php
//  PRICE HISTORY 
elseif ($action === 'price_history'):

    $stmt = $dbh->query("SELECT product_id, name FROM Products ORDER BY name");
    $products = $stmt->fetchAll();
    $selected_product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $history = [];

    if ($selected_product_id > 0) {
        $stmt = $dbh->prepare("
            SELECT time, action, old_price, new_price, employee_id
            FROM Product_history
            WHERE product_id = :pid
            ORDER BY time DESC
        ");
        $stmt->bindParam(":pid", $selected_product_id);
        $stmt->execute();
        $history = $stmt->fetchAll();
    }
    ?>

    <h3>Price History</h3>
    <form method="post" action="emp_main.php?action=price_history">
        <label>Product:</label><br>
        <select name="product_id" required>
            <option value="">-- Choose --</option>
            <?php foreach ($products as $p): ?>
                <option value="<?= $p['product_id'] ?>"
                    <?= ($selected_product_id == $p['product_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="View History">
    </form>

    <?php if ($selected_product_id && $history): ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>Time</th>
                <th>Action</th>
                <th>Old Price</th>
                <th>New Price</th>
                <th>% Change</th>
                <th>Employee ID</th>
            </tr>
            <?php foreach ($history as $h): ?>
                <?php
                $old = $h['old_price'];
                $new = $h['new_price'];
                $pct = ($old && $old != 0) ? (($new - $old) / $old) * 100 : null;
                ?>
                <tr>
                    <td><?= htmlspecialchars($h['time']) ?></td>
                    <td><?= htmlspecialchars($h['action']) ?></td>
                    <td><?= '$'.number_format($old, 2) ?></td>
                    <td><?= '$'.number_format($new, 2) ?></td>
                    <td><?= $pct !== null ? number_format($pct, 2).'%' : '' ?></td>
                    <td><?= htmlspecialchars($h['employee_id']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($selected_product_id): ?>
        <p>No price history records for this product.</p>
    <?php endif; ?>

<?php endif; ?>

</body>
</html>
