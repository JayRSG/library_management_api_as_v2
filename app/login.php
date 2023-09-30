<?php

function login_validator($data)
{
  if (((isset($data['email']) && isset($data['password'])) || isset($data['fingerprint_id'])) && isset($data['user_type']) && ($data['user_type'] == "user" || $data['user_type'] == "admin")) {
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
  response(['message' => 'Already Logged in'], 400);
  return;
}

if (!login_validator($_POST)) {
  response(['message' => 'Bad Request'], 400);
  return;
}

try {
  $email = $_POST['email'] ?? null;
  $password = $_POST['password'] ?? null;
  $fingerprint_id =  $_POST['fingerprint_id'] ?? null;

  $user_type = $_POST['user_type'] ?? null;   //admin|user

  if ($user_type == "admin") {
    if ($fingerprint_id) {
      $sql = "SELECT * FROM admin WHERE fingerprint_id = :fingerprint_id LIMIT 1";
    } else {
      $sql = "SELECT * FROM admin WHERE email = :email LIMIT 1";
    }
  } else if ($user_type == "user") {
    if ($fingerprint_id) {
      $sql = "SELECT * FROM user WHERE fingerprint_id = :fingerprint_id AND deleted IS NOT true LIMIT 1";
    } else {
      $sql = "SELECT * FROM user WHERE email = :email AND deleted IS NOT true LIMIT 1";
    }
  }

  $stmt = $conn->prepare($sql);

  if ($email) {
    $stmt->bindParam(':email', $email);
  } else if ($fingerprint_id) {
    $stmt->bindParam(':fingerprint_id', $fingerprint_id);
  }

  $result = $stmt->execute();

  // Fetch the user data

  if ($result && $stmt->rowCount() == 1) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    // User found, check the password or fingerprint_id
    if (password_verify($password, $user['password']) || ($fingerprint_id != NULL && $fingerprint_id === $user['fingerprint_id'])) {
      // Password or fingerprint_id matches, allow login
      $_SESSION['auth'] = $user;
      $_SESSION['auth_type'] = $user_type;
      response(['message' => "Login Successful"], 200);
    } else {
      // Password or fingerprint_id does not match
      response(["message" => "Invalid credentials"], 404);
    }
  } else {
    // User not found
    response(["message" => "User not found"], 404);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
