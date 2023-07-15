<?php

/**
 * Get user
 */

if (!checkGetMethod()) {
  return;
}

if (!checkUserType("user")) {
  return;
}

try {
  $user = auth();
  if ($user) {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, student_id, semester, department FROM user WHERE email = :email LIMIT 1");

    $stmt->bindParam(':email', $user['email']);

    $result = $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $stmt->rowCount() > 0) {
      response(["data" => $data], 200);
    } else {
      response(["message" => 'Not Found'], 404);
    }
  } else {
    response(['message' => "Unauthenticated"], 401);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
