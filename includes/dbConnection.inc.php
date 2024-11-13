<?php
// Vallant Felipe
// 04.09.2024
// LAP - Beispiel: Connection String


 $servername = "localhost:3307";
$database = "scooter_verleih";
$username = "root";
$password = "";

try {
  $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "<script>console.log('Connected successfully');</script>";
} catch(PDOException $e) {
  echo "<script>console.log('Connected failed');</script>";
  # echo "Connection failed: " . $e->getMessage();
}





