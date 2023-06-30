<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

/**
 * Get user
 */

if (!checkGetMethod()) {
  return;
}

if (!checkUserType("admin")) {
  return;
}

try {
  $user = auth();

  if ($user) {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email FROM admin WHERE email = :email");

    $stmt->bindParam(':email', $user['email']);

    $stmt->execute();

    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $data = $stmt->fetchObject();

    response(["data" => $data], 200);
  } else {
    response(["data" => "Unauthenticated"], 401);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
