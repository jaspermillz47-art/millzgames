<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$msg_type = 'success';
$upload_dir = '../millzgames/assets/images/products/';

// Create folder if not exists
if (!file_exists($upload_dir)) {
    @mkdir($upload_dir, 0777, true);
}

// ===== ADD PRODUCT =====
if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = (int)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $image_filename = null;

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $msg = 'Only JPG, PNG, GIF, WEBP allowed.';
            $msg_type = 'error';
        } elseif ($_FILES['image']['size'] > 3 * 1024 * 1024) {
            $msg = 'Image too large (max 3MB).';
            $msg_type = 'error';
        } else {
            $safe_name = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
            $image_filename = $safe_name . '-' . time() . '.' . $ext;
            $target = $upload_dir . $image_filename;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $msg = 'Upload failed.';
                $msg_type = 'error';
                $image_filename = null;
            }
        }
    }

    if (empty($msg) && empty($name)) {
        $msg = 'Name is required.';
        $msg_type = 'error';
    }

    if (empty($msg)) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("ssiis", $name, $desc, $price, $stock, $image_filename);
        if ($stmt->execute()) {
            $msg = '✅ Product added!';
        } else {
            $msg = 'Error: ' . $conn->error;
            $msg_type = 'error';
        }
    }
}

// ===== DELETE =====
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $res = $conn->query("SELECT image FROM products WHERE id = $id");
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['image']) && file_exists($upload_dir . $row['image'])) {
            @unlink($upload_dir . $row['image']);
        }
    }
    $conn->query("DELETE FROM products WHERE id = $id");
    header('Location: products.php'); exit;
}

// ===== UPDATE =====
if (isset($_POST['update_product'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = (int)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $status = $_POST['status'] ?? 'active';

    $res = $conn->query("SELECT image FROM products WHERE id = $id");
    $old = $res->fetch_assoc();
    $image_filename = $old['image'] ?? null;

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 3 * 1024 * 1024) {
            if (!empty($old['image']) && file_exists($upload_dir . $old['image'])) {
                @unlink($upload_dir . $old['image']);
            }
            $safe_name = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
            $image_filename = $safe_name . '-' . time() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_filename);
        }
    }

    if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        if (!empty($old['image']) && file_exists($upload_dir . $old['image'])) {
            @unlink($upload_dir . $old['image']);
        }
        $image_filename = null;
    }

    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, image=?, status=? WHERE id=?");
    $stmt->bind_param("ssiissi", $name, $desc, $price, $stock, $image_filename, $status, $id);
    $stmt->execute();
    header('Location: products.php'); exit;
}

$products = [];
$res = $conn->query("SELECT * FROM products ORDER BY id DESC");
while ($row = $res->fetch_assoc()) $products[] = $row;

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
}

$admin_name = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ ADMIN - Products</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bungee&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --bg: #000; --card: #0f0f0f; --border: #2a2a2a; --red: #ff0033; --red-light: #ff3366; --green: #00cc66; --text: #fff; --muted: #888; }
body { font-family: 'Rajdhani', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
@keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
.top-bar { background: linear-gradient(90deg, #0f0f0f, #000); border-bottom: 1px solid var(--border); padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
.top-bar .logo { font-family: 'Bungee', cursive; font-size: 20px; background: linear-gradient(90deg, #ff0033, #00cc66, #ff0033); background-size: 300% 100%; animation: gradientShift 4s ease infinite; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: 2px; }
.user-info { display: flex; align-items: center; gap: 12px; font-size: 13px; color: var(--muted); }
.user-info strong { color: var(--green); }
.back-btn, .logout-btn { padding: 7px 14px; background: transparent; border: 2px solid var(--red); color: var(--red); border-radius: 8px; font-family: 'Orbitron', sans-serif; font-size: 10px; font-weight: 900; letter-spacing: 1px; text-decoration: none; transition: 0.3s; }
.back-btn { border-color: var(--green); color: var(--green); }
.back-btn:hover { background: var(--green); color: #000; }
.logout-btn:hover { background: var(--red); color: #fff; }
.container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
.page-title { font-family: 'Orbitron', sans-serif; font-size: 24px; margin-bottom: 6px; }
.page-sub { color: var(--muted); font-size: 14px; margin-bottom: 30px; }
.alert { padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 14px; }
.alert.success { background: rgba(0,204,102,0.15); border: 1px solid var(--green); color: var(--green); }
.alert.error { background: rgba(255,0,51,0.15); border: 1px solid var(--red); color: var(--red-light); }
.form-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 28px; margin-bottom: 30px; position: relative; overflow: hidden; }
.form-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--red), var(--green), var(--red)); background-size: 200% 100%; animation: gradientShift 4s ease infinite; }
.form-title { font-family: 'Orbitron', sans-serif; font-size: 16px; margin-bottom: 20px; color: var(--green); }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px; }
.form-group label { display: block; font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; font-family: 'Orbitron', sans-serif; }
.form-group input[type="text"], .form-group input[type="number"], .form-group select, .form-group textarea {
    width: 100%; padding: 11px 14px; border-radius: 8px; border: 2px solid var(--border);
    background: #1a1a1a; color: #fff; font-size: 14px; font-family: 'Rajdhani', sans-serif;
}
.form-group textarea { resize: vertical; min-height: 70px; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--green); box-shadow: 0 0 15px rgba(0,204,102,0.3); }
.form-group input[type="file"] {
    width: 100%; padding: 10px; background: #1a1a1a; color: var(--text);
    border: 2px dashed var(--border); border-radius: 8px; cursor: pointer;
    font-size: 13px; font-family: 'Rajdhani', sans-serif;
}
.form-group input[type="file"]:hover { border-color: var(--green); }
.form-group input[type="file"]::file-selector-button {
    background: linear-gradient(90deg, var(--red), var(--green));
    color: #fff; border: none; padding: 8px 16px; border-radius: 6px;
    font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 10px;
    letter-spacing: 1px; cursor: pointer; margin-right: 12px;
}
.btn { padding: 12px 28px; border: none; border-radius: 8px; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 12px; letter-spacing: 1.5px; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; text-align: center; }
.btn-primary { background: linear-gradient(90deg, var(--red), var(--green), var(--red)); background-size: 300% 100%; animation: gradientShift 3s ease infinite; color: #fff; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,204,102,0.4); }
.btn-danger { background: transparent; border: 2px solid var(--red); color: var(--red); }
.btn-danger:hover { background: var(--red); color: #fff; }
.btn-small { padding: 7px 14px; font-size: 10px; }

.current-image { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 15px; background: #1a1a1a; border: 2px solid var(--border); border-radius: 10px; margin-bottom: 12px; }
.current-image img { width: 150px; height: 150px; border-radius: 12px; border: 3px solid var(--green); object-fit: cover; box-shadow: 0 0 20px rgba(0,204,102,0.4); }
.remove-check { display: flex; align-items: center; gap: 8px; margin-top: 8px; color: var(--red); font-size: 13px; cursor: pointer; }
.remove-check input { width: auto; cursor: pointer; }

.products-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
.product-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; transition: 0.3s; position: relative; }
.product-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--red), var(--green), var(--red)); background-size: 200% 100%; animation: gradientShift 4s ease infinite; z-index: 2; }
.product-card:hover { border-color: var(--green); transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,204,102,0.2); }
.product-img { height: 180px; background: linear-gradient(135deg, #111, #000); display: flex; align-items: center; justify-content: center; font-size: 60px; background-size: cover; background-position: center; position: relative; }
.product-img::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, transparent 60%, rgba(0,0,0,0.8)); }
.product-body { padding: 18px; }
.product-body h3 { font-family: 'Orbitron', sans-serif; font-size: 15px; color: #fff; margin-bottom: 6px; }
.product-desc { color: var(--muted); font-size: 12px; margin-bottom: 12px; min-height: 34px; }
.product-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.product-price { font-family: 'Orbitron', sans-serif; font-size: 18px; font-weight: 900; color: var(--green); }
.stock-badge { padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 900; font-family: 'Orbitron', sans-serif; }
.stock-in { background: rgba(0,204,102,0.2); color: var(--green); border: 1px solid var(--green); }
.stock-out { background: rgba(255,0,51,0.2); color: var(--red); border: 1px solid var(--red); }
.product-actions { display: flex; gap: 8px; }

.empty { text-align: center; padding: 60px 20px; background: var(--card); border: 2px dashed var(--border); border-radius: 16px; color: var(--muted); }
.empty .icon { font-size: 60px; margin-bottom: 12px; opacity: 0.6; }

@media (max-width: 700px) {
    .top-bar { padding: 12px 15px; }
    .top-bar .logo { font-size: 15px; }
    .user-info { font-size: 11px; gap: 6px; }
    .back-btn, .logout-btn { padding: 5px 10px; font-size: 9px; }
    .form-card { padding: 20px; }
    .page-title { font-size: 18px; }
}
</style>
</head>
<body>

<div class="top-bar">
    <div class="logo">MILLZ ADMIN</div>
    <div class="user-info">
        👤 <strong><?= htmlspecialchars($admin_name) ?></strong>
        <a href="dashboard.php" class="back-btn">← DASH</a>
        <a href="logout.php" class="logout-btn">LOGOUT</a>
    </div>
</div>

<div class="container">
    <h1 class="page-title">🛒 Manage Shop</h1>
    <p class="page-sub">Add, edit, and upload product images</p>

    <?php if (!empty($msg)): ?>
        <div class="alert <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="form-card">
        <h2 class="form-title"><?= $edit ? '✏️ Edit Product: ' . htmlspecialchars($edit['name']) : '➕ Add New Product' ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <?php if ($edit): ?>
                <input type="hidden" name="id" value="<?= $edit['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Product Name *</label>
                    <input type="text" name="name" value="<?= $edit ? htmlspecialchars($edit['name']) : '' ?>" placeholder="e.g. MILLZ Jersey" required>
                </div>
                <div class="form-group">
                    <label>Price (TZS)</label>
                    <input type="number" name="price" value="<?= $edit ? $edit['price'] : '' ?>" placeholder="25000" required>
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" value="<?= $edit ? $edit['stock'] : '10' ?>" placeholder="10">
                </div>
                <?php if ($edit): ?>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" <?= $edit['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $edit['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <?php endif; ?>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Description</label>
                    <textarea name="description" placeholder="Product description..."><?= $edit ? htmlspecialchars($edit['description']) : '' ?></textarea>
                </div>
            </div>

            <?php if ($edit && !empty($edit['image'])): ?>
                <div class="current-image">
                    <img src="../millzgames/assets/images/products/<?= htmlspecialchars($edit['image']) ?>" alt="Current image">
                    <p style="color: var(--muted); font-size: 12px;">Current: <?= htmlspecialchars($edit['image']) ?></p>
                    <label class="remove-check">
                        <input type="checkbox" name="remove_image" value="1">
                        ❌ Remove this image
                    </label>
                </div>
            <?php endif; ?>

            <div class="form-group" style="margin-bottom: 20px;">
                <label><?= $edit && !empty($edit['image']) ? '📷 Upload New Image (optional)' : '📷 Upload Product Image (optional)' ?></label>
                <input type="file" name="image" accept="image/*">
                <p style="color: var(--muted); font-size: 12px; margin-top: 6px;">Max 3MB · JPG, PNG, GIF, WEBP</p>
            </div>

            <?php if ($edit): ?>
                <button type="submit" name="update_product" class="btn btn-primary">💾 UPDATE</button>
                <a href="products.php" class="btn btn-danger" style="margin-left: 8px;">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_product" class="btn btn-primary">➕ ADD PRODUCT</button>
            <?php endif; ?>
        </form>
    </div>

    <h2 style="font-family: 'Orbitron', sans-serif; font-size: 16px; margin-bottom: 16px; color: var(--green);">📋 All Products (<?= count($products) ?>)</h2>

    <?php if (count($products) > 0): ?>
        <div class="products-grid">
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <div class="product-img" <?= !empty($p['image']) ? 'style="background-image:url(\'../millzgames/assets/images/products/' . htmlspecialchars($p['image']) . '\')"' : '' ?>>
                        <?php if (empty($p['image'])): ?>🎮<?php endif; ?>
                    </div>
                    <div class="product-body">
                        <h3><?= htmlspecialchars($p['name']) ?></h3>
                        <p class="product-desc"><?= htmlspecialchars($p['description'] ?? '') ?></p>
                        <div class="product-meta">
                            <span class="product-price">TZS <?= number_format($p['price']) ?></span>
                            <span class="stock-badge <?= $p['stock'] > 0 ? 'stock-in' : 'stock-out' ?>">
                                <?= $p['stock'] > 0 ? $p['stock'] . ' IN STOCK' : 'SOLD OUT' ?>
                            </span>
                        </div>
                        <div class="product-actions">
                            <a href="?edit=<?= $p['id'] ?>" class="btn btn-primary btn-small" style="flex: 1;">✏️ EDIT</a>
                            <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete product?')">🗑</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            <div class="icon">🛒</div>
            <p>No products yet. Add your first product above! ☝️</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>