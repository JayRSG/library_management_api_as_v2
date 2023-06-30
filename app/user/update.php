<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

if (!auth()) {
  header("location: /");
}

if (!checkPutMethod()) {
  return;
}

if (!checkUserType("user")) {
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

if (isset($_POST['update']) && $_POST['update'] == true) {
  try {
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $fingerprint = $_POST['fingerprint'] ?? null;

    $user = $_SESSION['auth'];

    // Check for user authorization
    if ($email != $user['email']) {
      response(['message' => 'Unauthorized'], 401);
      return;
    }

    if ((is_null($email) || is_null($password)) && is_null($fingerprint)) {
      response(['message' => "BAD Request. Check Credentials"], 400);
    } else {
      // Query to verify the user
      $verifyQuery = "SELECT * FROM `user` WHERE ";
      $params = [];

      if (!is_null($email)) {
        $verifyQuery .= "`email` = :email";
        $params[':email'] = $email;
      }

      if (!is_null($fingerprint)) {
        $verifyQuery .= "`fingerprint` = :fingerprint";
        $params[':fingerprint'] = $fingerprint;
      }

      $stmt = $conn->prepare($verifyQuery);

      foreach ($params as $param => $value) {
        $stmt->bindParam($param, $value);
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
      $updateQuery = "UPDATE `user` SET ";
      $updateParams = [];

      if (!empty($_POST['new_password']) && $_POST['password'] != $_POST['new_password']) {
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $updateQuery .= "`password` = :password, ";
        $updateParams[':password'] = $newPassword;
      }

      if (!empty($_POST['first_name'])) {
        $updateQuery .= "`first_name` = :first_name, ";
        $updateParams[':first_name'] = $_POST['first_name'];
      }

      if (!empty($_POST['last_name'])) {
        $updateQuery .= "`last_name` = :last_name, ";
        $updateParams[':last_name'] = $_POST['last_name'];
      }

      if (!empty($_POST['semester'])) {
        $updateQuery .= "`semester` = :semester, ";
        $updateParams[':semester'] = $_POST['semester'];
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
        response(['message' => "Update Failed"], 304);
      }
    }
  } catch (PDOException $e) {
    response(['message' => $e->getMessage()], 500);
  }
}
