<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require __DIR__ . "../../../config/config.php";

if (!auth()) {
  http_response_code(401);
  header("location: /");
}

/**
 * Delete a user
 */
if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
  $inputData  = file_get_contents("php://input");
  parse_str($inputData, $deleteData);

  $_POST = $deleteData;
}


if (isset($_POST['destroy']) && $_POST['destroy'] == true) {
  try {
    $user = auth();
    echo json_encode($_POST, JSON_PRETTY_PRINT);

    if ($user['email'] != $_POST['email']) {
      http_response_code(401);
      echo "Unathorized";
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
  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
}
