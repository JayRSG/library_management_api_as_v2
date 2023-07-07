<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

function pay_fine_validator($data)
{
  if (empty($data['user_id']) || empty($data['book_borrow_id']) || empty($data['fine'])) {
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
  $fine = $_POST['fine'];

  $sql = "UPDATE book_borrow SET late_fine = :fine, fine_payment_date = NOW() where id = :book_borrow_id AND user_id = :user_id ";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":fine", $fine);
  $stmt->bindParam(":book_borrow_id", $book_borrow_id);
  $stmt->bindParam(":user_id", $user_id);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => 'Payment Successful'], 200);
  } else {
    response(['message' => 'Payment Failed'], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
