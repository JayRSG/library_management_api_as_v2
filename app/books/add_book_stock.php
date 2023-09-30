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
  $conn->beginTransaction();


  $book_id = $_POST['book_id'];
  $rfids = $_POST['rfid'];

  $errors = [];

  $sql = "INSERT INTO book_rfid_rel (book_id, rfid) VALUES(:book_id, :rfid)";
  $stmt = $conn->prepare($sql);

  foreach ($rfids as $rfid) {
    $stmt->bindParam(":book_id", $book_id);
    $stmt->bindParam(":rfid", $rfid);

    $result = $stmt->execute();

    if (!$result) {
      array_push($errors, "Error in  $stmt->errorInfo()[2]");
    }
  }

  if (count($errors) < count($rfids)) {
    if (count($errors) > 0) {
      $conn->rollBack();
      response(['message' => "Failed due to errors", 'errors' => $errors]);
    } else {
      updateBookCount($conn, $book_id);
      $conn->commit();
      response(['message' => "Book(s) Added"]);
    }
  } else {
    $conn->rollBack();
    response(['message' => "Book Insertion Failed", 'errors' => $errors], 422);
  }
} catch (PDOException $e) {
  $conn->rollBack();
  response(['message' => $e->getMessage()], 500);
}
