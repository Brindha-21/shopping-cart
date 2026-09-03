<?php
require_once 'config.php';


if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($qty < 1) $qty = 1;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $qty;
    } else {
        $_SESSION['cart'][$product_id] = $qty;
    }

    $_SESSION['flash_success'] = "Product added to your cart!";
    header("Location: index.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 6");
$products = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="hero">
    <h1>Elevate Your Everyday Essentials</h1>
    <p>Discover handpicked premium electronics, fashion, and lifestyle gear with instant delivery.</p>
    <a href="products.php" class="btn btn-accent" style="font-size:1.05rem; padding:0.85rem 2rem;">
        <i class="fa-solid fa-store"></i> Browse All Products
    </a>
</div>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="font-size: 1.75rem; font-weight: 700;">Featured Products</h2>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Hand-selected items trending this week</p>
    </div>
    <a href="products.php" class="btn btn-outline">View All <i class="fa-solid fa-arrow-right"></i></a>
</div>

<div class="product-grid">
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <div class="product-image-wrap">
                <span class="product-category-badge"><?= htmlspecialchars($product['category']) ?></span>
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
            </div>
            <div class="product-body">
                <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                
                <div class="product-footer">
                    <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                    <form action="index.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <button type="submit" name="add_to_cart" class="btn btn-primary" style="padding: 0.5rem 1rem;">
                            <i class="fa-solid fa-cart-plus"></i> Add
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
