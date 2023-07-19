<?php

function borrow_book_validator($data)
{
  if (empty($data['rfid']) && empty($data['user_id'])) {
    return false;
  } else {
    return true;
  }
}

if (!checkPostMethod()) {
  return;
}

if (!borrow_book_validator($_POST)) {
  response(['message' => "Bad Request"], 400);
  return;
}

$user = auth();
if (!$user) {
  response(['message' => "Unauthenticated"], 401);
  return;
}

// Special Check if same user is returning his borrowed book, not applicable for admin
if (auth_type() == "user" && $user['id'] != $_POST['user_id']) {
  response(['message' => 'Unauthorized access']);
  return;
}


try {
  $rfid = $_POST['rfid'];
  $user_id = $_POST['user_id'];

  $sql = "SELECT * FROM book_rfid_rel where rfid = :rfid LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":rfid", $rfid);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    $rfid_info = $stmt->fetch(PDO::FETCH_ASSOC);

    $book_id = $rfid_info['book_id'];
    $rfid_rel_id = $rfid_info['id'];
    $issue_user_id = $user['id'];
    $issue_user_type = auth_type();

    $sql = "SELECT * FROM book_borrow WHERE  rfid_rel_id = :rfid_rel_id AND returned = 0 AND return_time IS NULL";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":rfid_rel_id", $rfid_rel_id);
    $result = $stmt->execute();

    // Book already issued and not returned yet
    if ($result && $stmt->rowCount() > 0) {
      response(['message' => "Book already issued and not returned yet"], 400);
      return;
    }

    $sql = "INSERT INTO book_borrow (user_id, book_id, rfid_rel_id, borrow_time, due_time, issue_user_id, issue_user_type) VALUES (:user_id, :book_id, :rfid_rel_id, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY), :issue_user_id, :issue_user_type)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":book_id", $book_id);
    $stmt->bindParam(":rfid_rel_id", $rfid_rel_id);
    $stmt->bindParam(":issue_user_id", $issue_user_id);
    $stmt->bindParam(":issue_user_type", $issue_user_type);

    $result = $stmt->execute();

    if ($result && $stmt->rowCount() > 0) {
      // Update Book Remaining_Qty
      $result = updateBookStock($conn, $book_id);
      if (!$result) {
        response(['message' => 'Book borrowed succesfully, Stock Update failed'], 200);
      } else {
        response(['message' => 'Book borrowed succesfully'], 200);
      }
    } else {
      response(['message' => 'Book Borrowing Failed'], 400);
    }
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
