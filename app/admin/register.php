<?php

function register_validator($data)
{
  if (
    isset($data['email']) && isset($data['password']) &&
    isset($data['first_name']) &&
    isset($data['last_name'])
  ) {
    return true;
  } else {
    return false;
  }
}

/**
 * Register Method
 */

if (!checkPostMethod()) {
  return;
}

if (auth()) {
  response(['message' => "Already Logged In"], 403);
  return;
}

if (!register_validator($_POST)) {
  response(['message' => "Bad Request"], 400);
  return;
}

try {
  $firstName = $_POST['first_name'] ?? null;
  $lastName = $_POST['last_name'] ?? null;
  $email = $_POST['email'] ?? null;
  $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
  $fingerprint = $_POST['fingerprint'] ?? null;

  $sql = "INSERT INTO `admin` (`first_name`, `last_name`, `email`, `password`)
  VALUES(:first_name, :last_name, :email, :pass)";

  $stmt = $conn->prepare($sql);

  $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
  $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->bindParam(':pass', $password, PDO::PARAM_STR);
  // $stmt->bindParam(':fingerprint', $fingerprint, PDO::PARAM_STR);

  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    response(['message' => "Successfully registered"], 200);
  } else {
    response(['message' => "Registration failed"], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
