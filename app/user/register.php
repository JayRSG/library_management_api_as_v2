<?php

function register_validator($data)
{
  if (
    !isset($data['first_name']) ||
    !isset($data['last_name']) ||
    !isset($data['email']) ||
    !isset($data['phone']) ||
    !isset($data['user_type_id']) ||
    ($data['user_type_id'] == 1 && !isset($data['student_id']))
  ) {
    return false;
  } else {
    return true;
  }
}

/**
 * Register Method
 */

if (!checkPostMethod()) {
  return;
}

if (!auth()) {
  response(['message' => "Unauthenticated"], 401);
  return;
}

if (!checkUserType('admin')) {
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
  $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : "";
  $user_type_id = $_POST['user_type_id'] ?? null;
  $fingerprint = $_POST['fingerprint'] ?? null;
  $password = password_hash('12345', PASSWORD_DEFAULT);

  $sql = "INSERT INTO `user` (`first_name`, `last_name`, `email`, `phone`, `password`, `student_id`, `user_type_id`) 
          VALUES (:first_name, :last_name, :email, :phone, :pass, :student_id, :user_type_id)";
  $stmt = $conn->prepare($sql);

  $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
  $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->bindParam(':pass', $password, PDO::PARAM_STR);
  $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
  $stmt->bindParam(':phone', $phone, PDO::PARAM_INT);
  $stmt->bindParam(':user_type_id', $user_type_id, PDO::PARAM_INT);
  // $stmt->bindParam(':fingerprint', $fingerprint, PDO::PARAM_STR);

  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    response(['message' => "Successfully registered!"], 200);
  } else {
    response(['message' => "Registration Failed"], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
