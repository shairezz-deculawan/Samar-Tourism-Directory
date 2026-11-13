<?php 
include 'config/database.php';
include 'includes/header.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT * FROM tourism_spots WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (spot_name LIKE ? OR location LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY spot_id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$spots = $stmt->get_result();

$total_spots = $conn->query("SELECT COUNT(*) as total FROM tourism_spots")->fetch_assoc()['total'];
$total_cats = $conn->query("SELECT COUNT(DISTINCT category) as total FROM tourism_spots")->fetch_assoc()['total'];
?>

<section class="hero">
    <h1>Explore top destinations in Samar</h1>
    <p>Discover waterfalls, beaches, rock formations, and caves across local communities.</p>
    <form action="index.php" method="GET" class="search-form">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by destination name or location...">
        <button type="submit" class="btn">Search</button>
    </form>
</section>

<section class="stats-grid">
    <div class="stat-card">
        <h2><?= $total_spots ?></h2>
        <p>Destinations Registered</p>
    </div>
    <div class="stat-card">
        <h2><?= $total_cats ?></h2>
        <p>Unique Categories</p>
    </div>
</section>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Tourism Directory</h2>
    <div>
        <a href="index.php" class="btn-sm btn-secondary">All Spots</a>
        <a href="index.php?category=Waterfalls" class="btn-sm">Waterfalls</a>
        <a href="index.php?category=Beaches" class="btn-sm">Beaches</a>
        <a href="index.php?category=Caves %26 Parks" class="btn-sm">Caves & Parks</a>
    </div>
</div>

<div class="card-grid">
    <?php if ($spots->num_rows > 0): ?>
        <?php while ($spot = $spots->fetch_assoc()): ?>
            <div class="card">
                <img src="assets/images/<?= htmlspecialchars($spot['image']) ?>" alt="<?= htmlspecialchars($spot['spot_name']) ?>" onerror="this.src='https://via.placeholder.com/300x180?text=Tourism+Spot'">
                <div class="card-body">
                    <span class="badge"><?= htmlspecialchars($spot['category']) ?></span>
                    <h3><?= htmlspecialchars($spot['spot_name']) ?></h3>
                    <p style="color:#555; margin: 5px 0;">📍 <?= htmlspecialchars($spot['location']) ?></p>
                    <p style="margin-bottom: 15px;"><strong>Entrance Fee:</strong> ₱<?= number_format($spot['entrance_fee'], 2) ?></p>
                    <a href="spot_details.php?id=<?= $spot['spot_id'] ?>" class="btn" style="margin-top: auto; text-align:center;">View Details</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No tourism destinations found matching your criteria.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
