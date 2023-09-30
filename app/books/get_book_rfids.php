<?php
function validate($data)
{
  return expect_keys($data, ['book_id']);
}

if (!checkPostMethod()) {
  return;
}

if (!checkUserType('admin')) {
  return;
}

try {
  $book_id = $_POST['book_id'];

  $sql = "SELECT rfid from book_rfid_rel where book_id = :book_id";
  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":book_id", $book_id);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($data) {
      response(['data' => $data], 200);
    }
  } else {
    response(['message' => "Not Found"], 404);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
