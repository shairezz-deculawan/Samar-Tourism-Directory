<?php
include 'config/database.php';
include 'includes/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM tourism_spots WHERE spot_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$spot = $stmt->get_result()->fetch_assoc();

if (!$spot) {
    echo "<h2>Destination not found.</h2><a href='index.php'>&larr; Back to directory</a>";
    include 'includes/footer.php';
    exit;
}
?>

<a href="index.php" class="btn-sm btn-secondary">&larr; Back to Directory</a>

<div class="spot-detail-card">
    <span class="badge"><?= htmlspecialchars($spot['category']) ?></span>
    <h1><?= htmlspecialchars($spot['spot_name']) ?></h1>
    <p style="color:#666;">📍 <?= htmlspecialchars($spot['location']) ?></p>

    <img src="assets/images/<?= htmlspecialchars($spot['image']) ?>" class="spot-detail-img" alt="<?= htmlspecialchars($spot['spot_name']) ?>" onerror="this.src='https://via.placeholder.com/800x400?text=Destination+Image'">

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <p><strong>Entrance Fee:</strong> ₱<?= number_format($spot['entrance_fee'], 2) ?></p>
        <p><strong>Opening Hours:</strong> <?= htmlspecialchars($spot['opening_hours']) ?></p>
    </div>

    <h3>Description</h3>
    <p><?= nl2br(htmlspecialchars($spot['description'])) ?></p>

    <?php if ($spot['has_indoor_nav']): ?>
        <div class="indoor-nav-box">
            <h3>🗺️ Indoor & Cave Section Navigation Guide</h3>
            <p style="margin-bottom: 10px;">Recommended interior route and chamber progression for visitors:</p>
            
            <div class="route-nodes">
                <span class="node">📍 1. Reception / Entrance</span> &rarr;
                <span class="node">📍 2. Main Chamber</span> &rarr;
                <span class="node">📍 3. Natural Bridge Arch</span> &rarr;
                <span class="node">📍 4. Subterranean River Dock</span>
            </div>

            <h4 style="margin-top: 15px;">Interior Route Breakdown:</h4>
            <p><?= nl2br(htmlspecialchars($spot['indoor_nav_details'] ?? 'Follow indoor trail markers and guided station indicators.')) ?></p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
