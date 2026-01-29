<?php
// view price change history for a product
require 'common.php';  
require_employee_login();

$dbh     = connectDB();
$history = [];

// Load products for dropdown
$stmt = $dbh->query("
    SELECT product_id, name
    FROM Products
    ORDER BY name
");
$products = $stmt->fetchAll();

// Handle product selection
$selected_product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($selected_product_id > 0) {
    $stmt = $dbh->prepare("
        SELECT time,
               old_price,
               new_price,
               old_quantity,
               new_quantity,
               employee_id,
               action
        FROM Product_history
        WHERE product_id = :pid
        ORDER BY time DESC
    ");
    $stmt->bindParam(":pid", $selected_product_id);
    $stmt->execute();
    $history = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<body>
<h2>Price History</h2>

<p><a href="emp_main.php">Back to Employee Main</a></p>

<form method="post" action="emp_price_history.php">
    <label>Select Product:</label><br>
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
    <h3>Price Changes</h3>
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
            $pct = ($old && $old != 0)
                ? (($new - $old) / $old) * 100
                : null;
            ?>
            <tr>
                <td><?= htmlspecialchars($h['time']) ?></td>
                <td><?= htmlspecialchars($h['action']) ?></td>
                <td><?= $old !== null ? '$'.number_format($old, 2) : '' ?></td>
                <td><?= $new !== null ? '$'.number_format($new, 2) : '' ?></td>
                <td>
                    <?php if ($pct !== null): ?>
                        <?= number_format($pct, 2) ?>%
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($h['employee_id']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php elseif ($selected_product_id): ?>
    <p>No price history records for this product.</p>
<?php endif; ?>

</body>
</html>
