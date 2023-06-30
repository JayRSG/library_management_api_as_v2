<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

/**
 * Get Books method
 */

if (!checkGetMethod()) {
  return;
}

if (!auth()) {
  response(['message' => "Unauthenticated"], 401);
  return;
}

try {
  $all = $_GET['all'] ?? null;
  $id = $_GET['id'] ?? null;
  $sql = "";

  if ($all) {
    $sql = "SELECT * FROM book";
    $stmt = $conn->prepare($sql);
  } else if ($id) {
    $sql = "SELECT * FROM book where id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);
  }

  $stmt->execute();

  if ($id) {
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
  } else if ($all) {
    $book = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }


  if ($stmt->rowCount() > 0) {
    response(['data' => $book], 200);
  } else {
    response(['message' => "Book Not Found"], 404);
  }
} catch (\PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
