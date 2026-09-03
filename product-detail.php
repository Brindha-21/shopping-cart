<?php
require_once 'config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['flash_error'] = "Product not found.";
    header("Location: products.php");
    exit();
}

if (isset($_POST['add_to_cart'])) {
    $qty = (int)($_POST['quantity'] ?? 1);
    if ($qty < 1) $qty = 1;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $qty;
    } else {
        $_SESSION['cart'][$product_id] = $qty;
    }

    $_SESSION['flash_success'] = "Added " . $qty . " item(s) to your cart!";
    header("Location: cart.php");
    exit();
}

require_once 'includes/header.php';
?>

<div style="background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm); display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-top: 1rem;">
    <div>
        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 100%; height: 380px; object-fit: cover; border-radius: var(--radius);">
    </div>

    <div style="display: flex; flex-direction: column;">
        <span style="background: var(--primary-light); color: var(--primary); font-size: 0.85rem; font-weight: 600; padding: 4px 12px; border-radius: 20px; width: fit-content; margin-bottom: 1rem;">
            <?= htmlspecialchars($product['category']) ?>
        </span>

        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--secondary);">
            <?= htmlspecialchars($product['name']) ?>
        </h1>

        <div style="font-size: 2rem; font-weight: 800; color: var(--primary); margin-bottom: 1.25rem;">
            $<?= number_format($product['price'], 2) ?>
        </div>

        <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.7; margin-bottom: 2rem; flex: 1;">
            <?= htmlspecialchars($product['description']) ?>
        </p>

        <div style="margin-bottom: 1.5rem; color: var(--text-muted); font-size: 0.9rem;">
            <i class="fa-solid fa-box" style="color: var(--accent);"></i> In Stock: <strong><?= $product['stock'] ?> units</strong>
        </div>

        <form action="product-detail.php?id=<?= $product['id'] ?>" method="POST" style="display: flex; gap: 1rem; align-items: center;">
            <div style="width: 100px;">
                <label for="quantity" style="font-size: 0.8rem; font-weight: 600; display: block; margin-bottom: 4px;">Quantity</label>
                <input type="number" id="quantity" name="quantity" class="form-control" value="1" min="1" max="<?= $product['stock'] ?>">
            </div>

            <button type="submit" name="add_to_cart" class="btn btn-primary btn-block" style="padding: 0.9rem 2rem; margin-top: auto;">
                <i class="fa-solid fa-cart-plus"></i> Add to Shopping Cart
            </button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
