<?php

/**
 * Get user
 */

if (!checkGetMethod()) {
  exit;
}

$user = auth();
if (!checkUserType("admin") && $user['id'] == 1) {
  return;
}

try {
  if ($user) {
    $stmt = $conn->prepare("SELECT admin.id, first_name, last_name, fingerprint_id, email, active FROM admin WHERE email = :email LIMIT 1");

    $stmt->bindParam(':email', $user['email']);

    $result = $stmt->execute();

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data && $stmt->rowCount() > 0) {
      response(["data" => $data], 200);
    } else {
      response(["message" => "Not Found"], 404);
    }
  } else {
    response(null, 401);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
