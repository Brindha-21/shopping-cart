<?php
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $pid => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$pid]);
            } else {
                $_SESSION['cart'][$pid] = $qty;
            }
        }
        $_SESSION['flash_success'] = "Cart updated successfully.";
    }
    header("Location: cart.php");
    exit();
}

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'remove' && isset($_GET['id'])) {
        $pid = (int)$_GET['id'];
        unset($_SESSION['cart'][$pid]);
        $_SESSION['flash_success'] = "Item removed from cart.";
    } elseif ($_GET['action'] === 'clear') {
        unset($_SESSION['cart']);
        $_SESSION['flash_success'] = "Cart cleared.";
    }
    header("Location: cart.php");
    exit();
}
$cart_items = [];
$subtotal = 0.00;

if (!empty($_SESSION['cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($_SESSION['cart']));
    $products = $stmt->fetchAll();

    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $item_total = $p['price'] * $qty;
        $subtotal += $item_total;

        $cart_items[] = [
            'product' => $p,
            'quantity' => $qty,
            'total' => $item_total
        ];
    }
}

$shipping = $subtotal > 0 ? 9.99 : 0.00;
$grand_total = $subtotal + $shipping;

require_once 'includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700;">Your Shopping Cart</h1>
    <p style="color: var(--text-muted);">Review items before proceeding to checkout</p>
</div>

<?php if (empty($cart_items)): ?>
    <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: var(--radius); border: 1px solid var(--border);">
        <i class="fa-solid fa-cart-arrow-down" style="font-size: 3.5rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
        <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Your cart is currently empty</h3>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Looks like you haven't added any products yet.</p>
        <a href="products.php" class="btn btn-primary">
            <i class="fa-solid fa-bag-shopping"></i> Start Shopping
        </a>
    </div>
<?php else: ?>
    <form action="cart.php" method="POST">
        <div class="cart-layout">
            <div>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <?php $p = $item['product']; ?>
                            <tr>
                                <td>
                                    <div class="cart-product-info">
                                        <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="cart-product-img">
                                        <div>
                                            <a href="product-detail.php?id=<?= $p['id'] ?>" style="font-weight: 600; color: var(--secondary);">
                                                <?= htmlspecialchars($p['name']) ?>
                                            </a>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($p['category']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-weight: 600;">$<?= number_format($p['price'], 2) ?></td>
                                <td>
                                    <input type="number" name="quantities[<?= $p['id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="<?= $p['stock'] ?>" class="form-control" style="width: 75px; text-align: center;">
                                </td>
                                <td style="font-weight: 700; color: var(--primary);">$<?= number_format($item['total'], 2) ?></td>
                                <td>
                                    <a href="cart.php?action=remove&id=<?= $p['id'] ?>" class="btn btn-danger btn-remove-item" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                    <button type="submit" name="update_cart" class="btn btn-outline">
                        <i class="fa-solid fa-arrows-rotate"></i> Update Quantities
                    </button>
                    <a href="cart.php?action=clear" class="btn btn-danger btn-remove-item">
                        <i class="fa-solid fa-trash"></i> Clear Cart
                    </a>
                </div>
            </div>

            <div class="cart-summary-card">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Flat Express Shipping</span>
                    <span>$<?= number_format($shipping, 2) ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>$<?= number_format($grand_total, 2) ?></span>
                </div>

                <a href="checkout.php" class="btn btn-accent btn-block" style="margin-top: 1.5rem; padding: 0.9rem;">
                    <i class="fa-solid fa-credit-card"></i> Proceed to Checkout
                </a>
                <a href="products.php" class="btn btn-outline btn-block" style="margin-top: 0.75rem;">
                    Continue Shopping
                </a>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
