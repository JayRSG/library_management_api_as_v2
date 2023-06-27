<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require __DIR__ . "../../../config/config.php";

if (!auth()) {
  http_response_code(401);
  header("location: /");
}

if (!checkDeleteMethod()) {
  return;
}

/**
 * Delete a user
 */
if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
  $inputData  = file_get_contents("php://input");
  parse_str($inputData, $deleteData);

  $_POST = $deleteData;
}

try {
  $user = auth();
  if ($user) {
    if ($user['email'] != $_POST['email']) {
      http_response_code(401);
      echo "Unauthorized";
      return;
    }

    $id = $_POST['id'];
    $sql = "DELETE FROM user WHERE email= :email";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(":email", $user['email']);
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
