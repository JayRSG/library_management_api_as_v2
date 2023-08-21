<?php

function books_validator($data)
{
  if (
    !isset($data['name']) ||
    !isset($data['author']) ||
    !isset($data['isbn']) ||
    !isset($data['call_number']) ||
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
  $edition = $_POST['edition'] ?? null;
  $isbn = $_POST['isbn'];
  $call_number = $_POST['call_number'];
  $publisher = $_POST['publisher'];
  $description = $_POST['description'] ?? null;

  $sql = "INSERT INTO `book` (`name`, `author`, `isbn`, `publisher`, `call_number`, `edition` , `description`) 
  VALUES (:name, :author, :isbn, :publisher, :call_number, :edition, :description)";

  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":name", $name);
  $stmt->bindParam(":author", $author);
  $stmt->bindParam(":isbn", $isbn);
  $stmt->bindParam(":edition", $edition);
  $stmt->bindParam(":call_number", $call_number);
  $stmt->bindParam(":publisher", $publisher);
  $stmt->bindParam(":description", $description);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => "Book inserted successfully"], 200);
  } else {
    response(['message' => "Book Insertion failed"], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
