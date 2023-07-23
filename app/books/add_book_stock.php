<?php

function validate($data)
{
  if (empty($data['book_id']) || empty($data['rfid'])) {
    return false;
  }

  return true;
}

if (!checkPostMethod()) {
  return;
}

$user = auth();
if (!$user) {
  response(['message' => 'Unauthenticated'], 401);
  return;
}

if (!checkUserType('admin')) {
  response(['message' => 'UnAuthorized'], 403);
  return;
}

if (!validate($_POST)) {
  response(['message' => "Bad Request"], 400);
  return;
}

try {
  $book_id = $_POST['book_id'];
  $rfid = $_POST['rfid'];

  $sql = "INSERT INTO book_rfid_rel (book_id, rfid) VALUES(:book_id, :rfid)";
  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":book_id", $book_id);
  $stmt->bindParam(":rfid", $rfid);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    updateBookCount($conn, $book_id);
    response(['message' => "Book Added"]);
  } else {
    response(['message' => "Book Insertion Failed"], 500);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
