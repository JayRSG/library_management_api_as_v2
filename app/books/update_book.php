<?php

if (!checkPutMethod()) {
  return;
}

if (!checkUserType('admin')) {
  return;
}

$user = auth();
if (!$user) {
  response(['message' => "Unauthenticated"], 401);
  return;
}

if ($_SERVER['REQUEST_METHOD'] == "PUT") {
  $putdata = file_get_contents("php://input");
  parse_str($putdata, $putParams);

  $_POST = $putParams;
}

if (isset($_POST['isbn']) && !isValidISBN13($_POST['isbn'])) {
  response(['message' => "Invalid ISBN number"], 400);
  return;
}

if (!isset($_GET['id']) && empty($_GET['id'])) {
  response(['message' => "Bad Request"], 400);
  return;
}

try {
  $findQuery = "SELECT `id` from `book` where id = :id";
  $stmt = $conn->prepare($findQuery);
  $stmt->bindParam(":id", $_GET['id']);
  $stmt->execute();

  $result = $stmt->fetch(PDO::FETCH_ASSOC);


  if ($stmt->rowCount() <= 0 && !$result) {
    response(['message' => "Book Not Found"], 404);
    return;
  }

  $sql = "UPDATE `book` SET ";
  $updateParams = [];

  if (isset($_POST['name']) && !empty($_POST['name'])) {
    $sql .= "`name` = :name, ";
    $updateParams[':name'] = $_POST['name'];
  }

  if (isset($_POST['author']) && !empty($_POST['author'])) {
    $sql .= "`author` = :author, ";
    $updateParams[':author'] = $_POST['author'];
  }

  if (isset($_POST['isbn']) && !empty($_POST['isbn'])) {
    $sql .= "`isbn` = :isbn, ";
    $updateParams[':isbn'] = $_POST['isbn'];
  }

  if (isset($_POST['description']) && !empty($_POST['description'])) {
    $sql .= "`description` = :description, ";
    $updateParams[':description'] = $_POST['description'];
  }

  if (isset($_POST['publisher']) && !empty($_POST['publisher'])) {
    $sql .= "`publisher` = :publisher, ";
    $updateParams[':publisher'] = $_POST['publisher'];
  }

  if (isset($_POST['quantity']) && !empty($_POST['quantity'])) {
    $sql .= "`quantity` = :quantity, ";
    $updateParams[':quantity'] = $_POST['quantity'];
  }

  if (isset($_POST['rfid']) && !empty($_POST['rfid'])) {
    $sql .= "`rfid` = :rfid, ";
    $updateParams[':rfid'] = $_POST['rfid'];
  }

  $sql = rtrim($sql, ', ');

  $sql .= " WHERE `id` = :id";
  $updateParams[':id'] = $_GET['id'];

  $updateStmt = $conn->prepare($sql);

  foreach ($updateParams as $param => $value) {
    $updateStmt->bindValue($param, $value);
  }

  $updateStmt->execute();

  if ($updateStmt->rowCount() > 0) {
    response(['message' => "Book updated"], 200);
  } else {
    response(['message' => "Book update failed"], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
