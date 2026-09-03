<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['flash_error'] = "Order not found.";
    header("Location: index.php");
    exit();
}

$itemStmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image as product_image 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$itemStmt->execute([$order_id]);
$items = $itemStmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="success-card">
    <div class="success-icon">
        <i class="fa-solid fa-circle-check"></i>
    </div>
    
    <h1 style="font-size: 2rem; font-weight: 800; color: var(--secondary); margin-bottom: 0.5rem;">
        Order Placed Successfully!
    </h1>
    <p style="color: var(--text-muted); font-size: 1.05rem; margin-bottom: 2rem;">
        Thank you for your purchase. We have received your order and are preparing it for shipment.
    </p>

    <div style="background: #f8fafc; border-radius: var(--radius); padding: 1.5rem; border: 1px solid var(--border); text-align: left; margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); margin-bottom: 1rem;">
            <div>
                <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Order Number</span>
                <div style="font-weight: 700; color: var(--primary); font-size: 1.1rem;">#ORD-<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></div>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Order Date</span>
                <div style="font-weight: 600;"><?= date('F j, Y', strtotime($order['created_at'])) ?></div>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Payment Method</span>
                <div style="font-weight: 600;"><?= htmlspecialchars($order['payment_method']) ?></div>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Status</span>
                <div><span style="background: #d1fae5; color: #065f46; font-size: 0.8rem; padding: 2px 10px; border-radius: 12px; font-weight: 600;"><?= htmlspecialchars($order['status']) ?></span></div>
            </div>
        </div>

        <div style="margin-bottom: 1rem;">
            <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Shipping Address</span>
            <div style="font-weight: 500; margin-top: 2px;">
                <strong><?= htmlspecialchars($order['full_name']) ?></strong><br>
                <?= htmlspecialchars($order['address']) ?>, <?= htmlspecialchars($order['city']) ?> - <?= htmlspecialchars($order['zip_code']) ?><br>
                Phone: <?= htmlspecialchars($order['phone']) ?>
            </div>
        </div>

        <h4 style="margin-top: 1.5rem; margin-bottom: 0.75rem; font-size: 1rem;">Purchased Items:</h4>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <?php foreach ($items as $item): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; background: white; padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <img src="<?= htmlspecialchars($item['product_image']) ?>" alt="" style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px;">
                        <div>
                            <strong style="font-size: 0.95rem;"><?= htmlspecialchars($item['product_name']) ?></strong>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Qty: <?= $item['quantity'] ?> × $<?= number_format($item['price'], 2) ?></div>
                        </div>
                    </div>
                    <div style="font-weight: 700; color: var(--secondary);">$<?= number_format($item['quantity'] * $item['price'], 2) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.25rem; padding-top: 1rem; border-top: 1.5px dashed var(--border); font-size: 1.2rem; font-weight: 800;">
            <span>Total Amount Paid</span>
            <span style="color: var(--primary);">$<?= number_format($order['total_amount'], 2) ?></span>
        </div>
    </div>

    <div style="display: flex; gap: 1rem; justify-content: center;">
        <a href="products.php" class="btn btn-primary">
            <i class="fa-solid fa-bag-shopping"></i> Continue Shopping
        </a>
        <button onclick="window.print()" class="btn btn-outline">
            <i class="fa-solid fa-print"></i> Print Receipt
        </button>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
