<?php
function validate($data)
{
  if (empty($data['book_id']) && empty($data['rfid'])) {
    return false;
  } else {
    return true;
  }
}

if (!checkPostMethod()) {
  return;
}

// if (!checkUserType('admin')) {
//   return;
// }

if (!validate($_POST)) {
  response(['message' => "Bad Request"], 400);
  return;
}

try {
  $book_id = $_POST['book_id'] ?? null;
  $book_rfid = $_POST['rfid'] ?? null;

  $sql = "";
  $param = array();

  if ($book_id) {
    $sql = "SELECT book_rfid_rel.id, book_rfid_rel.rfid, book.name from book_rfid_rel INNER JOIN book on book.id = book_rfid_rel.book_id where book_rfid_rel.book_id = :book_id";
    $param[':book_id']  = $book_id;
  } else if ($book_rfid) {
    $sql = "SELECT book_rfid_rel.id, book_rfid_rel.book_id, book.name from book_rfid_rel INNER JOIN book on book.id = book_rfid_rel.book_id where book_rfid_rel.rfid = :rfid LIMIT 1";
    $param[':rfid']  = $book_rfid;
  }

  $stmt = $conn->prepare($sql);

  foreach ($param as $key => $value) {
    $stmt->bindParam($key, $value);
  }

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    if ($book_id) {
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else if ($book_rfid) {
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($data) {
      response(['data' => $data], 200);
    }
  } else {
    response(['message' => "Not Found"], 404);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
