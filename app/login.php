<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

require __DIR__ . "../../config/config.php";

function login_validator($data)
{
  if (((isset($data['email']) && isset($data['password'])) || isset($data['fingerprint'])) && isset($data['user_type'])) {
    return true;
  } else {
    return false;
  }
}

/**
 * Login Method
 */

if (!checkPostMethod()) {
  return;
}

if (auth()) {
  http_response_code(200);
  echo "Already logged in";
  return;
}

if (!login_validator($_POST)) {
  http_response_code(400);
  echo "Bad Request";
  return;
}

try {
  $email = $_POST['email'] ?? null;
  $password = $_POST['password'] ?? null;
  $fingerprint =  $_POST['fingerprint'] ?? null;

  $user_type = $_POST['user_type'] ?? null;   //admin|user

  if ($user_type == "admin") {
    if ($fingerprint) {
      $sql = "SELECT * FROM admin WHERE fingerprint = :fingerprint AND NOT deleted = 1";
    } else {
      $sql = "SELECT * FROM admin WHERE email = :email AND NOT deleted = 1";
    }
  } else if ($user_type == "user") {
    if ($fingerprint) {
      $sql = "SELECT * FROM user WHERE fingerprint = :fingerprint AND NOT deleted = 1";
    } else {
      $sql = "SELECT * FROM user WHERE email = :email AND NOT deleted = 1";
    }
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
      $_SESSION['auth_type'] = $user_type;
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
