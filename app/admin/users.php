<?php

/**
 * Get users
 */

if (!checkGetMethod()) {
  return;
}

if (!checkUserType("admin")) {
  return;
}

try {
  $user = auth();
  if ($user && auth_type() == "admin") {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, student_id, semester, department from user");

    $stmt->execute();

    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $data = $stmt->fetchAll();

    response(["data" => $data], 200);
  } else {
    response(["data" => "Unauthenticated"], 401);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
