<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require __DIR__ . "../../../config/config.php";

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
  http_response_code(200);
  echo "Already logged in";
  return;
}

if (!register_validator($_POST)) {
  http_response_code(400);
  echo "Bad Request";
  return;
}

try {
  $firstName = $_POST['first_name'] ?? null;
  $lastName = $_POST['last_name'] ?? null;
  $email = $_POST['email'] ?? null;
  $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
  $fingerprint = $_POST['fingerprint'] ?? null;

  $sql = "INSERT INTO `admin` (`first_name`, `last_name`, `email`, `password`, `fingerprint`)
  VALUES(:first_name, :last_name, :email, :pass, :fingerprint)";

  $stmt = $conn->prepare($sql);

  $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
  $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->bindParam(':pass', $password, PDO::PARAM_STR);
  $stmt->bindParam(':fingerprint', $fingerprint, PDO::PARAM_STR);

  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    http_response_code(200);
    echo "Successfully registered";
  } else {
    http_response_code(400);
    echo "Registration failed";
  }
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
}
