<?php
// Turn on error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Step 1: PHP is working</h1>";

// Check if config file exists
$config_path = __DIR__ . '/config/db.php';
echo "<p>Looking for: <strong>" . $config_path . "</strong></p>";

if (!file_exists($config_path)) {
    die("<p style='color:red;'>❌ config/db.php NOT FOUND! Create it first.</p>");
}
echo "<p style='color:green;'>✅ config/db.php exists</p>";

// Include it
include $config_path;
echo "<p style='color:green;'>✅ db.php loaded</p>";

// Test connection
if (!isset($conn)) {
    die("<p style='color:red;'>❌ \$conn variable not created</p>");
}
echo "<p style='color:green;'>✅ Database connected</p>";

// Test query
$result = $conn->query("SELECT COUNT(*) as total FROM pros");
if (!$result) {
    die("<p style='color:red;'>❌ Query failed: " . $conn->error . "</p>");
}
$row = $result->fetch_assoc();
echo "<p style='color:green;'>✅ Pros in database: <strong>" . $row['total'] . "</strong></p>";

echo "<h2 style='color:green;'>🎮 All Good!</h2>";
?>