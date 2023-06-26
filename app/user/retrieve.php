<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require __DIR__ . "../../../config/config.php";

/**
 * Get user
 */

try {
  $user = $_SESSION['auth'];

  $stmt = $conn->prepare("SELECT id, first_name, last_name, email, student_id, semester, department FROM user WHERE email = :email");

  $stmt->bindParam(':email', $user['email']);

  $stmt->execute();

  $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
  $data = $stmt->fetchObject();

  echo json_encode($data, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
}
