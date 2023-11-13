<?php

function return_book_validator($data)
{
  if (
    empty($data['book_id']) &&
    empty($data['rfid_rel_id']) &&
    empty($data['user_id']) &&
    empty($data['id'])
  ) {
    return false;
  }

  return true;
}

// Method Check
if (!checkPostMethod()) {
  return;
}

// Request Validator
if (!return_book_validator($_POST)) {
  response(['message' => "Invalid Request"], 400);
  return;
}

// User Auth Validator
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
  $borrow_id = $_POST['id'];
  $user_id = $_POST['user_id'];
  $rfid_rel_id = $_POST['rfid_rel_id'];
  $book_id = $_POST['book_id'];

  $return_user_id = $user['id'];
  $return_user_type = auth_type();

  $fine_data = calculate_fine($conn, $data[0]['user_id'], $id);
  // $find_data[0] -> fine_info
  // $find_data[1] -> borrow_info

  if (
    (isset($fine_data[0]['fine']) && !empty($fine_data[1]['fine_excused']) && ($fine_data[1]['fine_excused'] != 1 || empty($fine_data[1]['fine_excused']))) ||
    (!empty($fine_data[0]['fine']) && !empty($fine_data[1]['late_fine']) && $fine_data[0]['fine'] - $fine_data[1]['late_fine'] > 0)
  ) {
    response(['message' => "Must pay late fine, please contact admin"], 200);
    return;
  }

  $sql = "UPDATE book_borrow set 
  return_time = NOW(), 
  return_user_id = :return_user_id, 
  return_user_type = :return_user_type, 
  returned = 1 
  WHERE 
  id = :borrow_id AND 
  user_id = :user_id AND 
  rfid_rel_id = :rfid_rel_id AND 
  book_id = :book_id AND 
  NOT returned = 1";

  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":borrow_id", $borrow_id);
  $stmt->bindParam(":user_id", $user_id);
  $stmt->bindParam(":rfid_rel_id", $rfid_rel_id);
  $stmt->bindParam(":book_id", $book_id);
  $stmt->bindParam(":return_user_id", $return_user_id);
  $stmt->bindParam(":return_user_type", $return_user_type);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => "Book returned"], 200);
  } else {
    response(['message' => "Book return failed"], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
