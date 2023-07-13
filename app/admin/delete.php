<?php

function delete_validator($data)
{
  if (
    isset($data['email']) && isset($data['password']) &&
    (isset($data['user_data']['email']) ||
      isset($data['user_data']['id']))
  ) {
    return true;
  } else {
    return false;
  }
}

if (!checkPostMethod()) {
  return;
}

if (!checkUserType("admin")) {
  response(['message' => "Unauthorized"], 401);
  return;
}


/**
 * Delete a user
 */

if (!delete_validator($_POST)) {
  response(['message' => "Bad Request"], 400);
  return;
}

try {
  $user = auth();
  $email = $_POST['email'] ?? null;
  $password = $_POST['password'] ?? null;
  $user_data['email'] = $_POST['user_data']['email'] ?? null;
  $user_data['id'] = $_POST['user_data']['id'] ?? null;


  if ($user) {
    if ($user['email'] != $email) {
      response(['message' => "Unauthorized"], 401);
      return;
    }

    $stmt = $conn->prepare("SELECT email, password from admin where email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    $user_verify = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!is_null($password) && !password_verify($password, $user_verify['password'])) {
      response(['message' => "Invalid Credentials"], 401);
      return;
    }

    if ($user_data['email'] && !$user_data['id']) {
      $sql = "UPDATE user SET deleted = 1 WHERE email = :email";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":email", $user_data['email']);
    } else if ($user_data['id'] && !$user_data['email']) {
      $sql = "UPDATE user SET deleted = 1 WHERE id = :user_id";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":user_id", $user_data['id']);
    } else {
      $sql = "UPDATE user SET deleted = 1 WHERE email = :email AND  id = :user_id";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":email", $user_data['email']);
      $stmt->bindParam(":user_id", $user_data['id']);
    }

    $stmt->execute();
    if ($stmt->rowCount() > 0) {
      response(['message' => "User deleted"], 200);
    } else {
      response(['message' => "Deletion failed"], 400);
    }
  } else {
    response(['message' => "Unauthenticated"], 401);
    return;
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
