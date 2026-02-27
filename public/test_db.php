<?php
echo "<h2>Test Koneksi Database</h2>";

$host = 'localhost';
$dbname = 'ssotoght_db_biss';
$username = 'ssotoght_tes1';
$password = 'vS7%RxL;!sExhghm';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "✅ Database TERHUBUNG!<br>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "✅ Jumlah users: " . $count;
    
} catch (PDOException $e) {
    echo "❌ Database ERROR: " . $e->getMessage();
}