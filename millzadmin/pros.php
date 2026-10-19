<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$msg_type = 'success';
$upload_dir = '../millzgames/assets/images/pros/';

// ===== ADD PRO =====
if (isset($_POST['add_pro'])) {
    $name = trim($_POST['name']);
    $price = (int)$_POST['price'];
    $color = $_POST['color'] ?? '#ff0033';
    $icon_filename = null;

    // Handle image upload
    if (!empty($_FILES['icon']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $msg = 'Only JPG, PNG, GIF, WEBP allowed.';
            $msg_type = 'error';
        } elseif ($_FILES['icon']['size'] > 3 * 1024 * 1024) {
            $msg = 'Image too large (max 3MB).';
            $msg_type = 'error';
        } else {
            $safe_name = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
            $icon_filename = $safe_name . '-' . time() . '.' . $ext;
            $target = $upload_dir . $icon_filename;

            if (!move_uploaded_file($_FILES['icon']['tmp_name'], $target)) {
                $msg = 'Upload failed. Check folder permissions.';
                $msg_type = 'error';
            }
        }
    }

    if (empty($msg) && empty($name)) {
        $msg = 'Name is required.';
        $msg_type = 'error';
    }

    if (empty($msg)) {
        $stmt = $conn->prepare("INSERT INTO pros (name, price, color, icon) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $name, $price, $color, $icon_filename);
        if ($stmt->execute()) {
            $msg = '✅ Pro added!';
        } else {
            $msg = 'Error: ' . $conn->error;
            $msg_type = 'error';
        }
    }
}

// ===== DELETE =====
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Delete image file too
    $res = $conn->query("SELECT icon FROM pros WHERE id = $id");
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['icon']) && file_exists($upload_dir . $row['icon'])) {
            @unlink($upload_dir . $row['icon']);
        }
    }

    $conn->query("DELETE FROM pros WHERE id = $id");
    header('Location: pros.php'); exit;
}

// ===== UPDATE PRO =====
if (isset($_POST['update_pro'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $price = (int)$_POST['price'];
    $color = $_POST['color'] ?? '#ff0033';

    // Get old icon
    $res = $conn->query("SELECT icon FROM pros WHERE id = $id");
    $old = $res->fetch_assoc();
    $icon_filename = $old['icon'] ?? null;

    // If new image uploaded — replace
    if (!empty($_FILES['icon']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $msg = 'Only JPG, PNG, GIF, WEBP allowed.';
            $msg_type = 'error';
        } elseif ($_FILES['icon']['size'] > 3 * 1024 * 1024) {
            $msg = 'Image too large (max 3MB).';
            $msg_type = 'error';
        } else {
            // Delete old file
            if (!empty($old['icon']) && file_exists($upload_dir . $old['icon'])) {
                @unlink($upload_dir . $old['icon']);
            }
            $safe_name = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
            $icon_filename = $safe_name . '-' . time() . '.' . $ext;
            $target = $upload_dir . $icon_filename;

            if (!move_uploaded_file($_FILES['icon']['tmp_name'], $target)) {
                $msg = 'Upload failed.';
                $msg_type = 'error';
                $icon_filename = $old['icon'];
            }
        }
    }

    // If "remove image" checked
    if (isset($_POST['remove_icon']) && $_POST['remove_icon'] === '1') {
        if (!empty($old['icon']) && file_exists($upload_dir . $old['icon'])) {
            @unlink($upload_dir . $old['icon']);
        }
        $icon_filename = null;
    }

    if (empty($msg)) {
        $stmt = $conn->prepare("UPDATE pros SET name=?, price=?, color=?, icon=? WHERE id=?");
        $stmt->bind_param("sissi", $name, $price, $color, $icon_filename, $id);
        $stmt->execute();
        header('Location: pros.php'); exit;
    }
}

$pros = [];
$res = $conn->query("SELECT * FROM pros ORDER BY id ASC");
while ($row = $res->fetch_assoc()) $pros[] = $row;

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit = $conn->query("SELECT * FROM pros WHERE id = $id")->fetch_assoc();
}

$admin_name = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ ADMIN - Pros</title>
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
.container { max-width: 1100px; margin: 0 auto; padding: 30px 20px; }
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
.form-group input[type="text"], .form-group input[type="number"], .form-group select { width: 100%; padding: 11px 14px; border-radius: 8px; border: 2px solid var(--border); background: #1a1a1a; color: #fff; font-size: 14px; font-family: 'Rajdhani', sans-serif; }
.form-group input:focus { outline: none; border-color: var(--green); box-shadow: 0 0 15px rgba(0,204,102,0.3); }
.form-group input[type="color"] { height: 45px; padding: 4px; cursor: pointer; width: 100%; border: 2px solid var(--border); border-radius: 8px; background: #1a1a1a; }
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

.current-image {
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    padding: 15px; background: #1a1a1a; border: 2px solid var(--border);
    border-radius: 10px; margin-bottom: 12px;
}
.current-image img {
    width: 100px; height: 100px; border-radius: 50%;
    border: 3px solid var(--green); object-fit: cover;
    box-shadow: 0 0 20px rgba(0,204,102,0.4);
}
.current-image p { color: var(--muted); font-size: 12px; }
.remove-check {
    display: flex; align-items: center; gap: 8px; margin-top: 8px;
    color: var(--red); font-size: 13px; cursor: pointer;
}
.remove-check input { width: auto; cursor: pointer; }

.pros-list { display: flex; flex-direction: column; gap: 12px; }
.pro-row { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 18px 22px; display: flex; align-items: center; gap: 18px; transition: 0.3s; }
.pro-row:hover { border-color: var(--green); transform: translateX(4px); }
.pro-avatar-wrap {
    width: 60px; height: 60px; border-radius: 50%; display: flex;
    align-items: center; justify-content: center;
    font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 22px;
    color: #fff; border: 3px solid var(--green); flex-shrink: 0;
    overflow: hidden; background: #111;
}
.pro-avatar-wrap img { width: 100%; height: 100%; object-fit: cover; }
.pro-body { flex: 1; min-width: 0; }
.pro-name { font-family: 'Orbitron', sans-serif; font-size: 16px; color: #fff; margin-bottom: 4px; }
.pro-price { color: var(--green); font-family: 'Orbitron', sans-serif; font-size: 14px; font-weight: 900; }
.pro-actions { display: flex; gap: 8px; }

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
    <h1 class="page-title">🎯 Manage Pros</h1>
    <p class="page-sub">Add, edit, and upload custom icons for each pro</p>

    <?php if (!empty($msg)): ?>
        <div class="alert <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="form-card">
        <h2 class="form-title"><?= $edit ? '✏️ Edit Pro: ' . htmlspecialchars($edit['name']) : '➕ Add New Pro' ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <?php if ($edit): ?>
                <input type="hidden" name="id" value="<?= $edit['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" value="<?= $edit ? htmlspecialchars($edit['name']) : '' ?>" placeholder="e.g. SKUDU" required>
                </div>
                <div class="form-group">
                    <label>Price (TZS)</label>
                    <input type="number" name="price" value="<?= $edit ? $edit['price'] : '1000' ?>" placeholder="1000">
                </div>
                <div class="form-group">
                    <label>Color</label>
                    <input type="color" name="color" value="<?= $edit ? htmlspecialchars($edit['color']) : '#ff0033' ?>">
                </div>
            </div>

            <?php if ($edit && !empty($edit['icon'])): ?>
                <div class="current-image">
                    <img src="../millzgames/assets/images/pros/<?= htmlspecialchars($edit['icon']) ?>" alt="Current icon">
                    <p>Current icon: <?= htmlspecialchars($edit['icon']) ?></p>
                    <label class="remove-check">
                        <input type="checkbox" name="remove_icon" value="1">
                        ❌ Remove this image (use default avatar)
                    </label>
                </div>
            <?php endif; ?>

            <div class="form-group" style="margin-bottom: 20px;">
                <label><?= $edit && !empty($edit['icon']) ? '📷 Upload New Image (optional)' : '📷 Upload Image (optional)' ?></label>
                <input type="file" name="icon" accept="image/*">
                <p style="color: var(--muted); font-size: 12px; margin-top: 6px;">
                    Max 3MB · JPG, PNG, GIF, WEBP · Square images work best
                </p>
            </div>

            <?php if ($edit): ?>
                <button type="submit" name="update_pro" class="btn btn-primary">💾 UPDATE</button>
                <a href="pros.php" class="btn btn-danger" style="margin-left: 8px;">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_pro" class="btn btn-primary">➕ ADD PRO</button>
            <?php endif; ?>
        </form>
    </div>

    <h2 style="font-family: 'Orbitron', sans-serif; font-size: 16px; margin-bottom: 16px; color: var(--green);">📋 All Pros (<?= count($pros) ?>)</h2>

    <div class="pros-list">
        <?php foreach ($pros as $p): ?>
            <div class="pro-row">
                <div class="pro-avatar-wrap" style="background: linear-gradient(135deg, <?= htmlspecialchars($p['color']) ?>, #000); border-color: <?= $p['icon'] ? 'var(--green)' : 'var(--red)' ?>;">
                    <?php if (!empty($p['icon']) && file_exists($upload_dir . $p['icon'])): ?>
                        <img src="../millzgames/assets/images/pros/<?= htmlspecialchars($p['icon']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    <?php else: ?>
                        <?= strtoupper(substr($p['name'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="pro-body">
                    <div class="pro-name">
                        <?= htmlspecialchars($p['name']) ?>
                        <?php if (!empty($p['icon'])): ?>
                            <span style="color: var(--green); font-size: 11px; margin-left: 8px;">✅ Custom icon</span>
                        <?php endif; ?>
                    </div>
                    <div class="pro-price">TZS <?= number_format($p['price']) ?></div>
                </div>
                <div class="pro-actions">
                    <a href="?edit=<?= $p['id'] ?>" class="btn btn-primary btn-small">✏️ EDIT</a>
                    <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete this pro?')">🗑</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>