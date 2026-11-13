<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

$spots = $conn->query("SELECT * FROM tourism_spots ORDER BY spot_id DESC");
include '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Admin Management Dashboard</h1>
    <a href="add_spot.php" class="btn">+ Add New Destination</a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Spot Name</th>
            <th>Location</th>
            <th>Category</th>
            <th>Fee (PHP)</th>
            <th>Indoor Nav</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($spots->num_rows > 0): ?>
            <?php while ($row = $spots->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['spot_id'] ?></td>
                    <td><strong><?= htmlspecialchars($row['spot_name']) ?></strong></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($row['category']) ?></span></td>
                    <td>₱<?= number_format($row['entrance_fee'], 2) ?></td>
                    <td><?= $row['has_indoor_nav'] ? '✅ Enabled' : '❌ Disabled' ?></td>
                    <td>
                        <a href="edit_spot.php?id=<?= $row['spot_id'] ?>" class="btn-sm">Edit</a>
                        <a href="delete_spot.php?id=<?= $row['spot_id'] ?>" class="btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No tourism destination records found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
