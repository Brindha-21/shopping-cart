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

    $_SESSION['flash_success'] = "Product added to cart!";
    header("Location: products.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$catStmt = $pdo->query("SELECT DISTINCT category FROM products");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">Explore All Products</h1>
    <p style="color: var(--text-muted);">Find the perfect item for your tech and lifestyle needs</p>
</div>

<div style="background: white; padding: 1.25rem; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
    <form action="products.php" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 240px;">
            <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
        </div>

        <div style="flex: 1; min-width: 180px;">
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass"></i> Filter
        </button>

        <?php if (!empty($search) || !empty($category)): ?>
            <a href="products.php" class="btn btn-outline">Reset</a>
        <?php endif; ?>
    </form>
</div>

<?php if (empty($products)): ?>
    <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: var(--radius); border: 1px solid var(--border);">
        <i class="fa-solid fa-box-open" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
        <h3 style="font-size: 1.25rem;">No products found</h3>
        <p style="color: var(--text-muted);">Try adjusting your search criteria or clearing filters.</p>
        <a href="products.php" class="btn btn-primary" style="margin-top: 1rem;">View All Products</a>
    </div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-image-wrap">
                    <span class="product-category-badge"><?= htmlspecialchars($product['category']) ?></span>
                    <a href="product-detail.php?id=<?= $product['id'] ?>">
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                    </a>
                </div>
                <div class="product-body">
                    <h3 class="product-title">
                        <a href="product-detail.php?id=<?= $product['id'] ?>">
                            <?= htmlspecialchars($product['name']) ?>
                        </a>
                    </h3>
                    <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                    
                    <div class="product-footer">
                        <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                        <form action="products.php" method="POST">
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
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
