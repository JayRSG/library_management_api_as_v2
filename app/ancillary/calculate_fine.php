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
function calculate_fine($date_diff)
{
  $fine = 0;
  $fine_per_day = 10;
  $response = [];

  if ($date_diff != null) {
    $fine = $date_diff * $fine_per_day;
    $response['fine'] = $fine;
    $response['validity'] = 14;
    $response['fined_days'] = $date_diff;
    $response['book_kept_for_days'] = $date_diff + 0;
  } else if ($date_diff <= 14) {
    $fine = 0;
    $response['fine'] = $fine;
  } else {
    return $response = "Invalid data";
  }

  $response['fine_per_day'] = $fine_per_day;
  return $response;
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

  $sql = "SELECT 
  u.first_name, 
  u.last_name, 
  u.student_id, 
  b.name, 
  b.author, 
  b.publisher, 
  bb.book_id, 
  bb.user_id, 
  bb.borrow_time, 
  bb.due_time, 
  bb.return_time, 
  bb.fine_payment_date, 
  bb.late_fine, 
  bb.returned, 
  NOW() as current_date_time, 
  DATEDIFF(bb.return_time, bb.due_time) as return_date_diff, 
  DATEDIFF(NOW(), bb.due_time) as current_date_diff
  FROM book_borrow bb
  INNER JOIN book b ON bb.book_id = b.id
  INNER JOIN user u ON bb.user_id = u.id
  WHERE bb.id = :book_borrow_id AND bb.user_id = :user_id  LIMIT 1";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":book_borrow_id", $book_borrow_id);
  $stmt->bindParam(":user_id", $user_id);
  $result = $stmt->execute();

  $fine_info = "";

  if ($result && $stmt->rowCount() > 0) {
    $borrow_info = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($borrow_info['returned_date_diff']) &&  $borrow_info['returned_date_diff'] > 0) {
      $fine_info = calculate_fine($borrow_info['return_date_diff']);
    } else if (!empty($borrow_info['current_date_diff']) && $borrow_info['current_date_diff'] > 0) {
      $fine_info = calculate_fine($borrow_info['current_date_diff']);
    }

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
