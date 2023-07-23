<?php

function register_validator($data)
{
  if (
    !isset($data['first_name']) ||
    !isset($data['last_name']) ||
    !isset($data['email']) ||
    !isset($data['password']) ||
    !isset($data['student_id']) ||
    !isset($data['phone']) ||
    !isset($data['department'])
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
  $student_id = $_POST['student_id'] ?? null;
  $fingerprint = $_POST['fingerprint'] ?? null;
  $semester = $_POST['phone'] ?? null;
  $department = $_POST['department'] ?? null;

  $sql = "INSERT INTO `user` (`first_name`, `last_name`, `email`, `phone`, `password`, `student_id`, `department`) 
          VALUES (:first_name, :last_name, :email, :phone, :pass, :student_id, :department)";
  $stmt = $conn->prepare($sql);

  $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
  $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->bindParam(':pass', $password, PDO::PARAM_STR);
  $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
  $stmt->bindParam(':phone', $phone, PDO::PARAM_INT);
  $stmt->bindParam(':department', $department, PDO::PARAM_STR);
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
