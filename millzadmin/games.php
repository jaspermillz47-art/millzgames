<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$msg_type = 'success';
$upload_dir = '../millzgames/assets/images/games/';

if (!file_exists($upload_dir)) { @mkdir($upload_dir, 0777, true); }

// ADD GAME
if (isset($_POST['add_game'])) {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $size = trim($_POST['file_size']);
    $features = trim($_POST['features']);
    $link = trim($_POST['download_link']);
    $image_filename = null;

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 3 * 1024 * 1024) {
            $safe = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
            $image_filename = $safe . '-' . time() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_filename);
        }
    }

    if (empty($name)) {
        $msg = 'Name is required.'; $msg_type = 'error';
    } else {
        $stmt = $conn->prepare("INSERT INTO games_store (name, description, file_size, features, download_link, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $desc, $size, $features, $link, $image_filename);
        if ($stmt->execute()) { $msg = '✅ Game added!'; }
        else { $msg = 'Error: ' . $conn->error; $msg_type = 'error'; }
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $res = $conn->query("SELECT image FROM games_store WHERE id = $id");
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['image']) && file_exists($upload_dir . $row['image'])) @unlink($upload_dir . $row['image']);
    }
    $conn->query("DELETE FROM games_store WHERE id = $id");
    header('Location: games.php'); exit;
}

// UPDATE
if (isset($_POST['update_game'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $size = trim($_POST['file_size']);
    $features = trim($_POST['features']);
    $link = trim($_POST['download_link']);

    $res = $conn->query("SELECT image FROM games_store WHERE id = $id");
    $old = $res->fetch_assoc();
    $image_filename = $old['image'] ?? null;

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 3 * 1024 * 1024) {
            if (!empty($old['image']) && file_exists($upload_dir . $old['image'])) @unlink($upload_dir . $old['image']);
            $safe = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
            $image_filename = $safe . '-' . time() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_filename);
        }
    }

    $stmt = $conn->prepare("UPDATE games_store SET name=?, description=?, file_size=?, features=?, download_link=?, image=? WHERE id=?");
    $stmt->bind_param("ssssssi", $name, $desc, $size, $features, $link, $image_filename, $id);
    $stmt->execute();
    header('Location: games.php'); exit;
}

$games = [];
$res = $conn->query("SELECT * FROM games_store ORDER BY id DESC");
while ($row = $res->fetch_assoc()) $games[] = $row;

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit = $conn->query("SELECT * FROM games_store WHERE id = $id")->fetch_assoc();
}

$admin_name = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ ADMIN - Manage Games</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bungee&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --bg: #1a0f00; --card: #2a1800; --border: #3a2400; --amber: #ff9500; --green: #00ff88; --red: #ff0033; --text: #fff; --muted: #b89860; }
body { font-family: 'Rajdhani', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
@keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
.top-bar { background: linear-gradient(90deg, #2a1800, #1a0f00); border-bottom: 1px solid var(--border); padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
.top-bar .logo { font-family: 'Bungee', cursive; font-size: 20px; color: var(--green); text-shadow: 0 0 15px rgba(0,255,136,0.5); letter-spacing: 2px; }
.user-info { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--muted); flex-wrap: wrap; }
.user-info strong { color: var(--green); }
.back-btn, .logout-btn { padding: 7px 14px; background: transparent; border: 2px solid var(--red); color: var(--red); border-radius: 8px; font-family: 'Orbitron', sans-serif; font-size: 10px; font-weight: 900; letter-spacing: 1px; text-decoration: none; transition: 0.3s; }
.back-btn { border-color: var(--green); color: var(--green); }
.back-btn:hover { background: var(--green); color: #000; }
.logout-btn:hover { background: var(--red); color: #fff; }
.container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
.page-title { font-family: 'Orbitron', sans-serif; font-size: 24px; margin-bottom: 6px; }
.page-sub { color: var(--muted); font-size: 14px; margin-bottom: 30px; }
.alert { padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 14px; }
.alert.success { background: rgba(0,255,136,0.15); border: 1px solid var(--green); color: var(--green); }
.alert.error { background: rgba(255,0,51,0.15); border: 1px solid var(--red); color: #ff6666; }
.card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 24px; margin-bottom: 24px; position: relative; overflow: hidden; }
.card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--amber), var(--green), var(--amber)); background-size: 200% 100%; animation: gradientShift 4s ease infinite; }
.card-title { font-family: 'Orbitron', sans-serif; font-size: 15px; margin-bottom: 18px; color: var(--green); letter-spacing: 1px; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 16px; }
.form-group label { display: block; font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; font-family: 'Orbitron', sans-serif; }
.form-group input, .form-group textarea { width: 100%; padding: 11px 14px; border-radius: 8px; border: 2px solid var(--border); background: #1a0f00; color: #fff; font-size: 14px; font-family: 'Rajdhani', sans-serif; }
.form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--green); box-shadow: 0 0 15px rgba(0,255,136,0.3); }
.form-group textarea { resize: vertical; min-height: 60px; }
.form-group input[type="file"] { padding: 10px; border: 2px dashed var(--border); cursor: pointer; }
.btn { padding: 12px 24px; border: none; border-radius: 8px; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 11px; letter-spacing: 1.5px; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; text-align: center; }
.btn-primary { background: linear-gradient(90deg, var(--green), #33ffa5, var(--green)); background-size: 300% 100%; animation: gradientShift 3s ease infinite; color: #000; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,255,136,0.4); }
.btn-danger { background: transparent; border: 2px solid var(--red); color: var(--red); }
.btn-danger:hover { background: var(--red); color: #fff; }
.btn-small { padding: 7px 14px; font-size: 10px; }
.games-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
.game-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; transition: 0.3s; }
.game-card:hover { border-color: var(--amber); transform: translateY(-5px); }
.game-img { height: 140px; background-size: cover; background-position: center; background-color: #1a0f00; display: flex; align-items: center; justify-content: center; font-size: 50px; }
.game-body { padding: 16px; }
.game-body h3 { font-family: 'Orbitron', sans-serif; font-size: 14px; color: #fff; margin-bottom: 6px; }
.game-body p { color: var(--muted); font-size: 12px; margin-bottom: 10px; }
.game-actions { display: flex; gap: 6px; }
@media (max-width: 700px) {
    .top-bar { padding: 12px 15px; }
    .top-bar .logo { font-size: 15px; }
    .back-btn, .logout-btn { padding: 5px 10px; font-size: 9px; }
    .page-title { font-size: 18px; }
    .card { padding: 18px; }
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
    <h1 class="page-title">🎮 Manage Games</h1>
    <p class="page-sub">Add games + download links. Users wata-unlock zote kwa TZS 5,000.</p>

    <?php if (!empty($msg)): ?>
        <div class="alert <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2 class="card-title"><?= $edit ? '✏️ Edit Game' : '➕ Add New Game' ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
            
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Game Name *</label>
                    <input type="text" name="name" value="<?= $edit ? htmlspecialchars($edit['name']) : '' ?>" placeholder="e.g. Bright Memory Infinite" required>
                </div>
                <div class="form-group">
                    <label>File Size</label>
                    <input type="text" name="file_size" value="<?= $edit ? htmlspecialchars($edit['file_size']) : '' ?>" placeholder="2.26 GB">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Description</label>
                    <textarea name="description" placeholder="Short description..."><?= $edit ? htmlspecialchars($edit['description']) : '' ?></textarea>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Features (separate with | )</label>
                    <textarea name="features" placeholder="Unlimited Health|Unlimited Ammo|All Weapons|No Recoil"><?= $edit ? htmlspecialchars($edit['features']) : '' ?></textarea>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Download Link (MediaFire / Drive)</label>
                    <input type="text" name="download_link" value="<?= $edit ? htmlspecialchars($edit['download_link']) : '' ?>" placeholder="https://www.mediafire.com/file/...">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Cover Image (max 3MB)</label>
                    <input type="file" name="image" accept="image/*">
                    <?php if ($edit && !empty($edit['image'])): ?>
                        <p style="color:var(--muted);font-size:12px;margin-top:6px;">Current: <?= htmlspecialchars($edit['image']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($edit): ?>
                <button type="submit" name="update_game" class="btn btn-primary">💾 UPDATE GAME</button>
                <a href="games.php" class="btn btn-danger" style="margin-left: 8px;">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_game" class="btn btn-primary">➕ ADD GAME</button>
            <?php endif; ?>
        </form>
    </div>

    <h2 style="font-family:'Orbitron';font-size:16px;margin-bottom:16px;color:var(--green);">📋 All Games (<?= count($games) ?>)</h2>

    <?php if (count($games) > 0): ?>
        <div class="games-grid">
            <?php foreach ($games as $g): ?>
                <div class="game-card">
                    <div class="game-img" <?= !empty($g['image']) ? 'style="background-image:url(\'../millzgames/assets/images/games/' . htmlspecialchars($g['image']) . '\')"' : '' ?>>
                        <?php if (empty($g['image'])): ?>🎮<?php endif; ?>
                    </div>
                    <div class="game-body">
                        <h3><?= htmlspecialchars($g['name']) ?></h3>
                        <p><?= htmlspecialchars($g['file_size'] ?? '') ?> · <?= htmlspecialchars(substr($g['description'] ?? '', 0, 60)) ?></p>
                        <div class="game-actions">
                            <a href="?edit=<?= $g['id'] ?>" class="btn btn-primary btn-small">✏️ Edit</a>
                            <a href="?delete=<?= $g['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete game?')">🗑</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <p style="color:var(--muted);text-align:center;padding:30px;">No games yet. Add your first game! ☝️</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html><?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$msg_type = 'success';
$upload_dir = '../millzgames/assets/images/games/';

if (!file_exists($upload_dir)) { @mkdir($upload_dir, 0777, true); }

// ADD GAME
if (isset($_POST['add_game'])) {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $size = trim($_POST['file_size']);
    $features = trim($_POST['features']);
    $link = trim($_POST['download_link']);
    $image_filename = null;

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 3 * 1024 * 1024) {
            $safe = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
            $image_filename = $safe . '-' . time() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_filename);
        }
    }

    if (empty($name)) {
        $msg = 'Name is required.'; $msg_type = 'error';
    } else {
        $stmt = $conn->prepare("INSERT INTO games_store (name, description, file_size, features, download_link, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $desc, $size, $features, $link, $image_filename);
        if ($stmt->execute()) { $msg = '✅ Game added!'; }
        else { $msg = 'Error: ' . $conn->error; $msg_type = 'error'; }
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $res = $conn->query("SELECT image FROM games_store WHERE id = $id");
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['image']) && file_exists($upload_dir . $row['image'])) @unlink($upload_dir . $row['image']);
    }
    $conn->query("DELETE FROM games_store WHERE id = $id");
    header('Location: games.php'); exit;
}

// UPDATE
if (isset($_POST['update_game'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $size = trim($_POST['file_size']);
    $features = trim($_POST['features']);
    $link = trim($_POST['download_link']);

    $res = $conn->query("SELECT image FROM games_store WHERE id = $id");
    $old = $res->fetch_assoc();
    $image_filename = $old['image'] ?? null;

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 3 * 1024 * 1024) {
            if (!empty($old['image']) && file_exists($upload_dir . $old['image'])) @unlink($upload_dir . $old['image']);
            $safe = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
            $image_filename = $safe . '-' . time() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_filename);
        }
    }

    $stmt = $conn->prepare("UPDATE games_store SET name=?, description=?, file_size=?, features=?, download_link=?, image=? WHERE id=?");
    $stmt->bind_param("ssssssi", $name, $desc, $size, $features, $link, $image_filename, $id);
    $stmt->execute();
    header('Location: games.php'); exit;
}

$games = [];
$res = $conn->query("SELECT * FROM games_store ORDER BY id DESC");
while ($row = $res->fetch_assoc()) $games[] = $row;

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit = $conn->query("SELECT * FROM games_store WHERE id = $id")->fetch_assoc();
}

$admin_name = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ ADMIN - Manage Games</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bungee&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --bg: #1a0f00; --card: #2a1800; --border: #3a2400; --amber: #ff9500; --green: #00ff88; --red: #ff0033; --text: #fff; --muted: #b89860; }
body { font-family: 'Rajdhani', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
@keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
.top-bar { background: linear-gradient(90deg, #2a1800, #1a0f00); border-bottom: 1px solid var(--border); padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
.top-bar .logo { font-family: 'Bungee', cursive; font-size: 20px; color: var(--green); text-shadow: 0 0 15px rgba(0,255,136,0.5); letter-spacing: 2px; }
.user-info { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--muted); flex-wrap: wrap; }
.user-info strong { color: var(--green); }
.back-btn, .logout-btn { padding: 7px 14px; background: transparent; border: 2px solid var(--red); color: var(--red); border-radius: 8px; font-family: 'Orbitron', sans-serif; font-size: 10px; font-weight: 900; letter-spacing: 1px; text-decoration: none; transition: 0.3s; }
.back-btn { border-color: var(--green); color: var(--green); }
.back-btn:hover { background: var(--green); color: #000; }
.logout-btn:hover { background: var(--red); color: #fff; }
.container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
.page-title { font-family: 'Orbitron', sans-serif; font-size: 24px; margin-bottom: 6px; }
.page-sub { color: var(--muted); font-size: 14px; margin-bottom: 30px; }
.alert { padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 14px; }
.alert.success { background: rgba(0,255,136,0.15); border: 1px solid var(--green); color: var(--green); }
.alert.error { background: rgba(255,0,51,0.15); border: 1px solid var(--red); color: #ff6666; }
.card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 24px; margin-bottom: 24px; position: relative; overflow: hidden; }
.card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--amber), var(--green), var(--amber)); background-size: 200% 100%; animation: gradientShift 4s ease infinite; }
.card-title { font-family: 'Orbitron', sans-serif; font-size: 15px; margin-bottom: 18px; color: var(--green); letter-spacing: 1px; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 16px; }
.form-group label { display: block; font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; font-family: 'Orbitron', sans-serif; }
.form-group input, .form-group textarea { width: 100%; padding: 11px 14px; border-radius: 8px; border: 2px solid var(--border); background: #1a0f00; color: #fff; font-size: 14px; font-family: 'Rajdhani', sans-serif; }
.form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--green); box-shadow: 0 0 15px rgba(0,255,136,0.3); }
.form-group textarea { resize: vertical; min-height: 60px; }
.form-group input[type="file"] { padding: 10px; border: 2px dashed var(--border); cursor: pointer; }
.btn { padding: 12px 24px; border: none; border-radius: 8px; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 11px; letter-spacing: 1.5px; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; text-align: center; }
.btn-primary { background: linear-gradient(90deg, var(--green), #33ffa5, var(--green)); background-size: 300% 100%; animation: gradientShift 3s ease infinite; color: #000; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,255,136,0.4); }
.btn-danger { background: transparent; border: 2px solid var(--red); color: var(--red); }
.btn-danger:hover { background: var(--red); color: #fff; }
.btn-small { padding: 7px 14px; font-size: 10px; }
.games-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
.game-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; transition: 0.3s; }
.game-card:hover { border-color: var(--amber); transform: translateY(-5px); }
.game-img { height: 140px; background-size: cover; background-position: center; background-color: #1a0f00; display: flex; align-items: center; justify-content: center; font-size: 50px; }
.game-body { padding: 16px; }
.game-body h3 { font-family: 'Orbitron', sans-serif; font-size: 14px; color: #fff; margin-bottom: 6px; }
.game-body p { color: var(--muted); font-size: 12px; margin-bottom: 10px; }
.game-actions { display: flex; gap: 6px; }
@media (max-width: 700px) {
    .top-bar { padding: 12px 15px; }
    .top-bar .logo { font-size: 15px; }
    .back-btn, .logout-btn { padding: 5px 10px; font-size: 9px; }
    .page-title { font-size: 18px; }
    .card { padding: 18px; }
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
    <h1 class="page-title">🎮 Manage Games</h1>
    <p class="page-sub">Add games + download links. Users wata-unlock zote kwa TZS 5,000.</p>

    <?php if (!empty($msg)): ?>
        <div class="alert <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2 class="card-title"><?= $edit ? '✏️ Edit Game' : '➕ Add New Game' ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
            
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Game Name *</label>
                    <input type="text" name="name" value="<?= $edit ? htmlspecialchars($edit['name']) : '' ?>" placeholder="e.g. Bright Memory Infinite" required>
                </div>
                <div class="form-group">
                    <label>File Size</label>
                    <input type="text" name="file_size" value="<?= $edit ? htmlspecialchars($edit['file_size']) : '' ?>" placeholder="2.26 GB">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Description</label>
                    <textarea name="description" placeholder="Short description..."><?= $edit ? htmlspecialchars($edit['description']) : '' ?></textarea>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Features (separate with | )</label>
                    <textarea name="features" placeholder="Unlimited Health|Unlimited Ammo|All Weapons|No Recoil"><?= $edit ? htmlspecialchars($edit['features']) : '' ?></textarea>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Download Link (MediaFire / Drive)</label>
                    <input type="text" name="download_link" value="<?= $edit ? htmlspecialchars($edit['download_link']) : '' ?>" placeholder="https://www.mediafire.com/file/...">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Cover Image (max 3MB)</label>
                    <input type="file" name="image" accept="image/*">
                    <?php if ($edit && !empty($edit['image'])): ?>
                        <p style="color:var(--muted);font-size:12px;margin-top:6px;">Current: <?= htmlspecialchars($edit['image']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($edit): ?>
                <button type="submit" name="update_game" class="btn btn-primary">💾 UPDATE GAME</button>
                <a href="games.php" class="btn btn-danger" style="margin-left: 8px;">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_game" class="btn btn-primary">➕ ADD GAME</button>
            <?php endif; ?>
        </form>
    </div>

    <h2 style="font-family:'Orbitron';font-size:16px;margin-bottom:16px;color:var(--green);">📋 All Games (<?= count($games) ?>)</h2>

    <?php if (count($games) > 0): ?>
        <div class="games-grid">
            <?php foreach ($games as $g): ?>
                <div class="game-card">
                    <div class="game-img" <?= !empty($g['image']) ? 'style="background-image:url(\'../millzgames/assets/images/games/' . htmlspecialchars($g['image']) . '\')"' : '' ?>>
                        <?php if (empty($g['image'])): ?>🎮<?php endif; ?>
                    </div>
                    <div class="game-body">
                        <h3><?= htmlspecialchars($g['name']) ?></h3>
                        <p><?= htmlspecialchars($g['file_size'] ?? '') ?> · <?= htmlspecialchars(substr($g['description'] ?? '', 0, 60)) ?></p>
                        <div class="game-actions">
                            <a href="?edit=<?= $g['id'] ?>" class="btn btn-primary btn-small">✏️ Edit</a>
                            <a href="?delete=<?= $g['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete game?')">🗑</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <p style="color:var(--muted);text-align:center;padding:30px;">No games yet. Add your first game! ☝️</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>