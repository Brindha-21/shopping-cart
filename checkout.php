<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = "Please log in or sign up to complete your order.";
    header("Location: login.php?redirect=checkout.php");
    exit();
}
if (empty($_SESSION['cart'])) {
    $_SESSION['flash_error'] = "Your cart is empty.";
    header("Location: products.php");
    exit();
}

$placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute(array_keys($_SESSION['cart']));
$products = $stmt->fetchAll();

$subtotal = 0;
$cart_details = [];
foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p['id']];
    $item_total = $p['price'] * $qty;
    $subtotal += $item_total;
    $cart_details[] = [
        'product' => $p,
        'quantity' => $qty,
        'price' => $p['price'],
        'total' => $item_total
    ];
}

$shipping = 9.99;
$grand_total = $subtotal + $shipping;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim($_POST['full_name'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $city       = trim($_POST['city'] ?? '');
    $zip_code   = trim($_POST['zip_code'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $payment    = trim($_POST['payment_method'] ?? 'Cash on Delivery');

    if (empty($full_name) || empty($address) || empty($city) || empty($zip_code) || empty($phone)) {
        $error = "Please fill in all shipping details.";
    } else {
        try {
            // Begin Transaction
            $pdo->beginTransaction();

            // Insert into orders table
            $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, full_name, address, city, zip_code, phone, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $orderStmt->execute([
                $_SESSION['user_id'],
                $grand_total,
                $full_name,
                $address,
                $city,
                $zip_code,
                $phone,
                $payment
            ]);

            $order_id = $pdo->lastInsertId();

            // Insert each item into order_items table
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cart_details as $item) {
                $itemStmt->execute([
                    $order_id,
                    $item['product']['id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            header("Location: order-success.php?id=" . $order_id);
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Order processing failed: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700;">Checkout & Shipping</h1>
    <p style="color: var(--text-muted);">Provide your delivery details to place your order</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form action="checkout.php" method="POST">
    <div class="cart-layout">
        <!-- Shipping Address Form -->
        <div style="background: white; padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--secondary);">
                <i class="fa-solid fa-truck" style="color: var(--primary);"></i> Shipping Information
            </h3>

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control" placeholder="John Doe" required value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="address">Street Address</label>
                <textarea id="address" name="address" class="form-control" rows="3" placeholder="123 Main Street, Apt 4B" required></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" class="form-control" placeholder="New York" required>
                </div>
                <div class="form-group">
                    <label for="zip_code">ZIP / Postal Code</label>
                    <input type="text" id="zip_code" name="zip_code" class="form-control" placeholder="10001" required>
                </div>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" placeholder="+1 (555) 000-1234" required>
            </div>

            <h3 style="font-size: 1.25rem; font-weight: 700; margin: 2rem 0 1rem; color: var(--secondary);">
                <i class="fa-solid fa-credit-card" style="color: var(--primary);"></i> Payment Method
            </h3>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border: 1.5px solid var(--border); border-radius: var(--radius); cursor: pointer; background: #f8fafc;">
                    <input type="radio" name="payment_method" value="Cash on Delivery" checked>
                    <span><strong>Cash on Delivery (COD)</strong> - Pay when package arrives</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border: 1.5px solid var(--border); border-radius: var(--radius); cursor: pointer; background: #f8fafc;">
                    <input type="radio" name="payment_method" value="Credit / Debit Card">
                    <span><strong>Credit or Debit Card</strong> (Demo simulated)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border: 1.5px solid var(--border); border-radius: var(--radius); cursor: pointer; background: #f8fafc;">
                    <input type="radio" name="payment_method" value="UPI / Online Banking">
                    <span><strong>UPI / Net Banking</strong> (Demo simulated)</span>
                </label>
            </div>
        </div>

        <div class="cart-summary-card">
            <h3>Items in Order</h3>

            <div style="max-height: 250px; overflow-y: auto; margin-bottom: 1rem; padding-right: 0.5rem;">
                <?php foreach ($cart_details as $item): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; font-size: 0.9rem;">
                        <div>
                            <strong><?= htmlspecialchars($item['product']['name']) ?></strong>
                            <div style="color: var(--text-muted);">Qty: <?= $item['quantity'] ?> × $<?= number_format($item['price'], 2) ?></div>
                        </div>
                        <div style="font-weight: 600;">$<?= number_format($item['total'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-row">
                <span>Subtotal</span>
                <span>$<?= number_format($subtotal, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Flat Express Shipping</span>
                <span>$<?= number_format($shipping, 2) ?></span>
            </div>
            <div class="summary-row total">
                <span>Grand Total</span>
                <span>$<?= number_format($grand_total, 2) ?></span>
            </div>

            <button type="submit" class="btn btn-accent btn-block" style="margin-top: 1.5rem; padding: 1rem; font-size: 1.05rem;">
                <i class="fa-solid fa-lock"></i> Place Order Now
            </button>
        </div>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?>
