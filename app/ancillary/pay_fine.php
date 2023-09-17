<?php

function pay_fine_validator($data)
{
  if (empty($data['user_id']) || empty($data['book_borrow_id'])) {
    return false;
  } else {
    return true;
  }
}

// Method Check
if (!checkPostMethod()) {
  return;
}

if (!checkUserType('admin')) {
  return;
}

if (!pay_fine_validator($_POST)) {
  response(['message' => "Bad Request"], 400);
  return;
}


try {
  $user_id = $_POST['user_id'];
  $book_borrow_id = $_POST['book_borrow_id'];
  $fine = $_POST['fine'] ?? NULL;
  $fine_excused = $_POST['fine_excused'] == "true" ? true : ($_POST['fine_excused'] == "false" ? false : NULL);
  $late_fine_pending = $_POST['late_fine_pending'] ?? NULL;

  $sql = "UPDATE book_borrow SET late_fine = :fine, fine_excused = :fine_excused, late_fine_pending = :late_fine_pending, fine_payment_date = NOW() where id = :book_borrow_id AND user_id = :user_id ";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":fine", $fine);
  $stmt->bindParam(":book_borrow_id", $book_borrow_id);
  $stmt->bindParam(":user_id", $user_id);
  $stmt->bindParam(":fine_excused", $fine_excused);
  $stmt->bindParam(":late_fine_pending", $late_fine_pending);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => 'Payment Successful'], 200);
  } else {
    response(['message' => 'Payment Failed'], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
