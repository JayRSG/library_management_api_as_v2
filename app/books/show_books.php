<?php

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
  $search_term = $_GET['search_term'] ?? null;
  $sql = "";
  $params = [];
  $stmt = "";
  $result = "";

  if ($all) {
    $sql = "SELECT * FROM book";
  } else if ($id || $search_term) {
    $sql = "SELECT * FROM book where ";
    if ($id) {
      $sql .= "id = :id LIMIT 1";
    } else if ($search_term) {
      $sql .= "name LIKE :search_term OR author LIKE :search_term OR publisher LIKE :search_term OR isbn = :search_term";
    }
  }

  $sql = rtrim($sql, "OR ");

  if ($sql != "") {
    $stmt = $conn->prepare($sql);
    if ($id) {
      $stmt->bindParam(':id', $id);
    }
    if ($search_term) {
      $stmt->bindValue(':search_term', '%' . $search_term . '%');
    }
    $result = $stmt->execute();
    if ($id) {
      $book = $stmt->fetch(PDO::FETCH_ASSOC);
    } else if ($search_term) {
      $book = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
  }

  if ($result && $stmt->rowCount() > 0) {
    response(['data' => $book], 200);
  } else {
    response(['message' => "Book Not Found", $sql], 404);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage(), $sql], 500);
}
