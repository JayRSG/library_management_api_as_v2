<?php
function validate($data)
{
  if (auth_type() == "admin") {
    if (empty($data['book_borrow_id']) && empty($data['user_id'])) {
      return false;
    }
  }
  return true;
}

// user auth validator
if (!auth()) {
  response(['message' => "Unauthenticated"], 401);
  return;
}

// Method Check
if (!checkPostMethod()) {
  return;
}


if (!validate($_POST)) {
  response(['message' => 'Bad Request'], 400);
  return;
}

try {
  $user = auth();
  if (auth_type() == "user") {
    $user_id = $user['id'];
  } else {
    $user_id = $_POST['user_id'];
  }
  $book_borrow_id = $_POST['book_borrow_id'];

  $result = calculate_fine($conn, $user_id, $book_borrow_id);

  if (count($result) > 0) {
    $fine_info = $result[0];
    $borrow_info = $result[1];

    response([
      'data' =>
      [
        "fine_info" => $fine_info, "borrow_info" => $borrow_info
      ]
    ], 200);
  } else {
    response(['message' => "Not Found"], 404);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
