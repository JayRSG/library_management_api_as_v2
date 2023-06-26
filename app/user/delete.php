<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require __DIR__ . "../../../config/config.php";

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
    $user = $_SESSION['auth'];

    echo json_encode($_POST, JSON_PRETTY_PRINT);

    if ($user['email'] != $_POST['email']) {
      http_response_code(401);
      echo "Unathorized";
      return;
    }

    // $id = $_POST['id'];
    // $sql = "DELETE FROM user WHERE id='$id'";
    // $conn->exec($sql);
    // echo "User deleted";
  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
}
