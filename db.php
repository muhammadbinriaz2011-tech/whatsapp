<?php
$host = 'db.dbrjk9vggjx7v0.supabase.co';
$port = '5432';
$dbname = 'postgres';
$user = 'umt2dcztkf6rt';
$password = 'd89k0usi6geq';
 
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
