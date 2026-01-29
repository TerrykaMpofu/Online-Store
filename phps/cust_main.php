<?php
require 'common.php';   // session_start

$dbh = connectDB();

//Load all categories
$stmt = $dbh->query("SELECT name FROM Categories ORDER BY name");
$categories = $stmt->fetchAll();

// Check if a category was selected
$selected_category = $_POST['category_name'] ?? null;
$products = [];

if ($selected_category) {
    $stmt = $dbh->prepare("
        SELECT product_id, name, price, stock_qty, discontinued, image
        FROM Products
        WHERE category_name = :cat
        ORDER BY name
    ");
    $stmt->bindParam(":cat", $selected_category);
    $stmt->execute();
    $products = $stmt->fetchAll();
}

$dbh = null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Jimmy's Store</title>
</head>
<body>

<h2>Jimmy's Store - Browse Products</h2>

<?php if (isset($_SESSION['customer_id'])): ?>
    <!-- AFTER customer login -->
    <p>
        Welcome, <?= htmlspecialchars($_SESSION['username']) ?> |
        <a href="cust_login.php?action=change_password">Change Password</a> |
        <a href="cust_login.php?action=logout">Logout</a> |
        <a href="emp_login.php">Employee Login</a>
    </p>
<?php else: ?>
    <!-- BEFORE login -->
    <p>
        <a href="cust_login.php?action=login">Customer Login</a> |
        <a href="register.php">Register</a> |
        <a href="emp_login.php">Employee Login</a>
    </p>
<?php endif; ?>

<!-- Category selection form -->
<form method="post" action="cust_main.php">
    <label>Select Category:</label>
    <select name="category_name" required>
        <option value="">-- Choose --</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['name']) ?>"
                <?= ($selected_category === $cat['name']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="submit" value="View Products">
</form>

<?php if ($selected_category && $products): ?>
    <h3>Products in "<?= htmlspecialchars($selected_category) ?>"</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Price</th>
            <th>In Stock</th>
            <?php if (isset($_SESSION['customer_id'])): ?>
                <th>Add to Cart</th>
            <?php endif; ?>
        </tr>
        <?php foreach ($products as $p): ?>
            <tr>
                <!-- Image cell -->
                <td>
                    <?php if (!empty($p['image'])): ?>
                        <img src="<?= htmlspecialchars($p['image']) ?>"
                             alt="<?= htmlspecialchars($p['name']) ?>"
                             style="max-width:100px; max-height:100px;">
                    <?php endif; ?>
                </td>

                <!-- Name / price / stock -->
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td>$<?= number_format($p['price'], 2) ?></td>
                <td><?= (int)$p['stock_qty'] ?></td>

                <!-- Add-to-cart column only if logged in -->
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <td>
                        <!-- Implement add_to_cart.php later -->
                        <form method="post" action="add_to_cart.php">
                            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                            <input type="number" name="qty"
                                   min="1" max="<?= (int)$p['stock_qty'] ?>"
                                   value="1" required>
                            <input type="submit" value="Add">
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
<?php elseif ($selected_category): ?>
    <p>No products in this category.</p>
<?php endif; ?>

</body>
</html>
