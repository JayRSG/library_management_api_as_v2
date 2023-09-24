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
    $stmt = $conn->prepare("SELECT user.id, first_name, last_name, email, phone, fingerprint, student_id, user_type.user_type FROM user
      INNER JOIN user_type on user.user_type_id = user_type.id
      WHERE email = :email LIMIT 1");

    $stmt->bindParam(':email', $user['email']);

    $result = $stmt->execute();

    if ($result && $stmt->rowCount() > 0) {

      $data = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($data['student_id']) {
        $student_info = extractStudentInfo($data['student_id']);
        $data['student_info'] = $student_info;
      }

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
