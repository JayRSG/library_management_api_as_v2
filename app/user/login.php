<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require __DIR__ . "../../../config/config.php";

/**
 * Login Method
 */

if ($_SERVER['REQUEST_METHOD'] != "POST") {
  http_response_code(403);
  echo "Forbidden";
  return;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  try {
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $fingerprint =  $_POST['fingerprint'] ?? null;

    if (auth()) {
      http_response_code(200);
      echo "Already logged in";
      return;
    }

    if ($fingerprint) {
      $sql = "SELECT * FROM user WHERE fingerprint = :fingerprint";
    } else {
      $sql = "SELECT * FROM user WHERE email = :email";
    }

    $stmt = $conn->prepare($sql);

    if ($email) {
      $stmt->bindParam(':email', $email);
    } else if ($fingerprint) {
      $stmt->bindParam(':fingerprint', $fingerprint);
    }

    $stmt->execute();

    // Fetch the user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
      // User found, check the password or fingerprint
      if (password_verify($password, $user['password']) || $fingerprint === $user['fingerprint']) {
        // Password or fingerprint matches, allow login
        echo "Login successful!";
        $_SESSION['auth'] = $user;
      } else {
        // Password or fingerprint does not match
        echo "Invalid credentials.";
      }
    } else {
      // User not found
      echo "Invalid credentials.";
    }
  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
}
