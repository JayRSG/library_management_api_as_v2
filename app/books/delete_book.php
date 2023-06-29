<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require __DIR__ . "../../../config/config.php";


if (!checkDeleteMethod()) {
  return;
}

if (!checkUserType("admin")) {
  response(['message' => "Unauthorized"], 403);
  return;
}

try {
  $id = $_GET['id'];
  $sql = "DELETE from book where id = :id";
  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":id", $id);
  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => "Deleted Successfully"], 200);
  } else {
    response(['message' => "Book Deleted Failed"], 200);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
