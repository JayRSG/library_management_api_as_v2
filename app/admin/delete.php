<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require __DIR__ . "../../../config/config.php";

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

if (!auth()) {
  http_response_code(401);
  header("location: /");
}

if (!checkPostMethod()) {
  return;
}

if (!checkUserType("admin")) {
  http_response_code(403);
  echo "Unauthorized";
  return;
}


/**
 * Delete a user
 */

if (!delete_validator($_POST)) {
  http_response_code(400);
  echo "Bad Request";
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
      http_response_code(401);
      echo "Unauthorized";
      return;
    }

    $stmt = $conn->prepare("SELECT email, password from admin where email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    $user_verify = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!is_null($password) && !password_verify($password, $user_verify['password'])) {
      http_response_code(401);
      echo "Invalid Credentials";
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
      http_response_code(200);
      echo "User deleted";
    } else {
      http_response_code(400);
      echo "Deletion failed";
    }
  } else {
    http_response_code(401);
    echo "Unauthenticated";
    return;
  }
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
}
