<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

function books_validator($data)
{
  if (
    !isset($data['name']) ||
    !isset($data['author']) ||
    !isset($data['isbn']) ||
    !isset($data['publisher'])
  ) {
    return false;
  }

  return true;
}

/**
 * Add books method
 */

if (!checkPostMethod()) {
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

if (!books_validator($_POST)) {
  response(['data' => "Bad Request"], 400);
  return;
}

if (!isValidISBN13($_POST['isbn'])) {
  response(['message' => "Invalid ISBN number"], 400);
  return;
}

try {
  $name = $_POST['name'];
  $author = $_POST['author'];
  $isbn = $_POST['isbn'];
  $publisher = $_POST['publisher'];
  $description = $_POST['description'] ?? null;
  $quantity = $_POST['quantity'] ?? null;
  $rfid = $_POST['rfid'] ?? null;

  $sql = "INSERT INTO `book` (`name`, `author`, `isbn`, `publisher`, `quantity`, `description`, `rfid`) 
  VALUES (:name, :author, :isbn, :publisher, :quantity, :description, :rfid)";

  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":name", $name);
  $stmt->bindParam(":author", $author);
  $stmt->bindParam(":isbn", $isbn);
  $stmt->bindParam(":publisher", $publisher);
  $stmt->bindParam(":quantity", $quantity);
  $stmt->bindParam(":description", $description);
  $stmt->bindParam(":rfid", $rfid);

  $result = $stmt->execute();

  if ($stmt->rowCount() > 0) {
    response(['message' => "Book inserted successfully"], 200);
  } else {
    response(['message' => "Book Insertion failed"], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
