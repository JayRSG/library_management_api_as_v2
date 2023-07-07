<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

function calculate_fine($return_date_diff, $current_date_diff)
{
  $fine = 0;
  $fine_per_day = 10;
  $response = [];

  if ($return_date_diff != null && $return_date_diff > 14) {
    $fine = ($return_date_diff - 14) * $fine_per_day;
    $response['fine'] = $fine;
    $response['validity'] = 14;
    $response['fined_days'] = $return_date_diff - 14;
    $response['book_kept_for_days'] = $return_date_diff + 0;
  } else if ($return_date_diff == null && $current_date_diff != null && $current_date_diff > 14) {
    $fine = ($current_date_diff - 14) * $fine_per_day;
    $response['fine'] = $fine;
    $response['validity'] = 14;
    $response['fined_days'] = $current_date_diff - 14;
    $response['book_kept_for_days'] = $current_date_diff + 0;
  } else if ($return_date_diff <= 14 || $current_date_diff <= 14) {
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

if (!checkUserType('admin')) {
  return;
}

// Method Check
if (!checkPostMethod()) {
  return;
}

try {
  $user_id = $_POST['user_id'];
  $book_borrow_id = $_POST['book_borrow_id'];

  $sql = "SELECT bb.book_id, b.name, b.author, b.publisher, bb.user_id, u.first_name, u.last_name, u.student_id, bb.borrow_time, bb.due_time, bb.return_time, bb.late_fine, fine_payment_date, bb.returned, NOW() as current_date_time, DATEDIFF(bb.return_time, bb.borrow_time) as return_date_diff, DATEDIFF(NOW(), bb.borrow_time) as current_date_diff
  FROM book_borrow bb
  INNER JOIN book b ON bb.book_id = b.id
  INNER JOIN user u ON bb.user_id = u.id
  WHERE bb.id = :book_borrow_id AND bb.user_id = :user_id  LIMIT 1";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":book_borrow_id", $book_borrow_id);
  $stmt->bindParam(":user_id", $user_id);
  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    $borrow_info = $stmt->fetch(PDO::FETCH_ASSOC);

    response(['data' => ["fine_info" => calculate_fine($borrow_info['return_date_diff'], $borrow_info['current_date_diff']), "borrow_info" => $borrow_info]], 200);
  } else {
    response(['message' => "Not Found"], 404);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
