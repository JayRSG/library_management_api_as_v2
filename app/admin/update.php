<?php

function validate($data)
{
  if (empty($data['email']) || empty($data['password']) || empty($data['confirm_password']) || $data['new_password'] != $data['confirm_password']) {
    return false;
  }

  return true;
}

$user = auth();
if (!$user) {
  response(['message' => 'Unauthenticated'], 401);
  return;
}

if (!checkPutMethod()) {
  return;
}

if (!checkUserType("admin")) {
  return;
}

/**
 * Update User Information
 */


if ($_SERVER['REQUEST_METHOD'] == "PUT") {
  $putdata = file_get_contents("php://input");
  parse_str($putdata, $putParams);

  $_POST = $putParams;
}

if (!validate($_POST)) {
  response(['message' => "BAD Request"], 400);
  return;
}


try {
  $email = $_POST['email'] ?? null;
  $password = $_POST['password'] ?? null;
  $fingerprint = $_POST['fingerprint'] ?? null;


  // Check for user authorization
  if ($email != $user['email']) {
    response(['message' => 'Bad Request'], 400);
    return;
  }

  // Query to verify the user
  $verifyQuery = "SELECT * FROM `admin` WHERE ";
  $params = [];

  if (!is_null($email)) {
    $verifyQuery .= "`email` = :email AND ";
    $params[':email'] = $email;
  }

  $verifyQuery = rtrim($verifyQuery, "AND ");

  $stmt = $conn->prepare($verifyQuery);

  foreach ($params as $param => $value) {
    $stmt->bindValue($param, $value);
  }

  $stmt->execute();

  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    response(['message' => "User not found"], 404);
    return;
  }

  // Check password if provided
  if (!is_null($password) && !password_verify($password, $user['password'])) {
    response(['message' => "Invalid password"], 401);
    return;
  }

  // Update the user fields
  $updateQuery = "UPDATE `admin` SET ";
  $updateParams = [];

  if (!empty($_POST['new_password']) && $_POST['password'] != $_POST['new_password']) {
    $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $updateQuery .= "`password` = :password, ";
    $updateParams[':password'] = $newPassword;
  }

  // Remove trailing comma and space
  $updateQuery = rtrim($updateQuery, ', ');

  $updateQuery .= " WHERE `email` = :email";
  $updateParams[':email'] = $user['email'];

  $updateStmt = $conn->prepare($updateQuery);

  foreach ($updateParams as $param => $value) {
    $updateStmt->bindValue($param, $value);
  }

  $updateStmt->execute();

  if ($updateStmt->rowCount() > 0) {
    response(['message' => "Successfully updated"], 200);
  } else {
    response(['message' => "Update Failed"], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
