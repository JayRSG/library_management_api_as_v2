<?php

function auth()
{
  if (isset($_SESSION['auth'])) {
    return $_SESSION['auth'];
  } else {
    return false;
  }
}

function auth_type()
{
  if (isset($_SESSION['auth_type'])) {
    return $_SESSION['auth_type'];
  } else {
    return false;
  }
}

function checkUserType($type)
{
  if (auth() && auth_type() != $type) {
    response("", 401);
    return false;
  }

  return true;
}

function checkPostMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "POST") {
    response(['message' => "Method Not allowed"], 405);
    return false;
  }
  return true;
}

function checkGetMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "GET") {
    response(['message' => "Method Not allowed"], 405);
    return false;
  }
  return true;
}

function checkPutMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "PUT") {
    response(['message' => "Method Not allowed"], 405);
    return false;
  }
  return true;
}

function checkDeleteMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "DELETE") {
    response(['message' => "Method Not allowed"], 405);
    return false;
  }
  return true;
}

function response($response, $code = 200, $headers = null)
{
  $headers = [
    'Access-Control-Allow-Origin' => 'http://libraryman.com',
    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
    'Access-Control-Allow-Headers' => 'Content-Type',
    'Access-Control-Allow-Credentials' => 'true'
  ];

  if ($headers != null) {
    foreach ($headers as $name => $data) {
      header("$name: $data");
    }
  }

  http_response_code($code);
  if ($response != "") {
    echo json_encode($response, JSON_PRETTY_PRINT);
  }
}

function isValidISBN13($isbn)
{
  // Remove any dashes or spaces from the input
  $isbn = str_replace(['-', ' '], '', $isbn);

  // Check if the input is a valid ISBN-13 format
  if (!preg_match('/^\d{13}$/', $isbn)) {
    return false;
  }

  // Calculate the checksum digit
  $sum = 0;
  for ($i = 0; $i < 12; $i++) {
    $sum += ($i % 2 === 0) ? (int)$isbn[$i] : (int)$isbn[$i] * 3;
  }
  $checksum = (10 - ($sum % 10)) % 10;

  // Compare the calculated checksum with the last digit of the ISBN-13
  return $checksum === (int)$isbn[12];
}

function updateBookStock($conn, $id)
{
  try {
    $sql = "UPDATE book
    SET remaining_qty = (
      (SELECT COUNT(*) from book_rfid_rel where book_id = :id) - (SELECT COUNT(*) 
      FROM book_borrow 
      WHERE book_borrow.book_id = book.id AND returned = 0)
    ) WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);
    $result = $stmt->execute();
  } catch (PDOException $e) {
    return $e;
  }

  return $result;
}

function updateBookCount($conn, $book_id)
{
  $result = null;
  try {

    $sql = "UPDATE book SET quantity = (SELECT COUNT(*) from book_rfid_rel WHERE book_id = :book_id), remaining_qty = quantity - (SELECT COUNT(*) FROM book_borrow where book_id = :book_id AND returned = 0) where id = :book_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":book_id", $book_id);
    $result = $stmt->execute();
  } catch (PDOException $e) {
    return $e->getMessage();
  }

  return $result;
}

function get_late_fine($date_diff, $late_fine)
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

function calculate_fine($conn, $user_id, $book_borrow_id)
{
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

    if (!empty($borrow_info['return_date_diff']) && $borrow_info['return_date_diff'] > 0) {
      $fine_info = get_late_fine($borrow_info['return_date_diff'], $borrow_info['late_fine']);
    } else if (!empty($borrow_info['current_date_diff']) && $borrow_info['current_date_diff'] > 0) {
      $fine_info = get_late_fine($borrow_info['current_date_diff'], $borrow_info['late_fine']);
    }

    return [$fine_info, $borrow_info];
  } else {
    return false;
  }
}


function extractStudentInfo($studentId)
{
  $year = substr($studentId, 0, 2);
  $semesterCode = substr($studentId, 2, 1);
  $departmentCode = substr($studentId, 3, 2);
  $studentNumber = substr($studentId, 5, 3);

  $semesterMap = [
    '1' => 'Autumn',
    '2' => 'Spring',
    '3' => 'Summer'
  ];

  $departmentMap = [
    '01' => 'School of Business',
    '02' => 'School of Science and Engineering',
    '03' => 'School of Liberal Arts and Social Sciences',
    '04' => 'School of Law'
  ];

  $semester = isset($semesterMap[$semesterCode]) ? $semesterMap[$semesterCode] : 'Unknown';
  $department = isset($departmentMap[$departmentCode]) ? $departmentMap[$departmentCode] : 'Unknown';

  $studentInfo = [
    'admission_year' => 2000 + $year,
    'admission_semester' => $semester,
    'department' => $department,
    'studentNumber' => $studentNumber
  ];

  return $studentInfo;
}


function expect_keys($data, $expected_keys)
{
  if ($data) {
    foreach ($data as $key => $value) {
      if (!in_array($key, $expected_keys) && empty($value)) {
        return false;
      }
    }
  } else {
    return false;
  }

  return true;
}
