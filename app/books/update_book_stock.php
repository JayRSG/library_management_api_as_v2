<?php

if (!checkGetMethod()) {
  return;
}

if (!checkUserType('admin')) {
  return;
}

try {
  $book_id = $_GET['book_id'] ?? null;

  if (!$book_id) {
    response(['message' => 'Bad Request'], 400);
  }

  $book_count_result = updateBookCount($conn, $book_id);
  $book_stock_result = updateBookStock($conn, $book_id);

  if ($book_count_result && $book_stock_result) {
    response(['message' => 'Updated Book Stock Status']);
  } else {
    response(['message' => $book_count_result . "," . $book_stock_result], 500);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
