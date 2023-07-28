<?php


function borrow_list_validator($data)
{
  if (
    empty($data['all']) &&
    empty($data['id']) &&
    empty($data['user_id']) &&
    empty($data['book_id']) &&
    // empty($data['rfid_rel_id']) &&

    (empty($data['date_from']) || empty($data['date_to']))
  ) {
    return false;
  }
  return true;
}

if (!checkGetMethod()) {
  return;
}

$user = auth();
if (!$user) {
  response(['message' => "Unauthenticated"], 401);
  return;
}


if (!borrow_list_validator($_GET)) {
  response(['message' => "Bad Request"], 400);
  return;
}


try {
  $id = $_GET['id'] ?? null;
  $all = $_GET['all'] ?? null;

  $book_id = $_GET['book_id'] ?? null;
  $borrow_date = $_GET['date_from'] ?? null;
  $borrow_date_range = $_GET['date_to'] ?? null;
  $returned = isset($_GET['returned']) && $_GET['returned'] != "" && ($_GET['returned'] == 1 || $_GET['returned'] == 0) ? $_GET['returned'] : -1;

  $user_id = $_GET['user_id'] ?? null;
  if (auth_type() == "user" && ($all || $user_id != $user['id'])) {
    response(['message' => 'Not Found'], 404);
    return;
  }


  $sql = "SELECT book_borrow.*, 
  issue_admin.first_name as issuer_admin_first_name, issue_admin.last_name as issuer_admin_last_name, 
  issue_user.first_name issuer_user_first_name, issue_user.last_name issuer_user_last_name, 
  book.name, book.author, book.publisher, 
  user.first_name, user.last_name, user.id as user_id,
  return_admin.first_name as return_admin_first_name, return_admin.last_name as return_admin_last_name, 
  return_user.first_name as return_user_first_name, return_user.last_name as return_user_last_name 
  
  FROM book_borrow 
  INNER JOIN book ON book.id = book_id
INNER JOIN user ON user.id = book_borrow.user_id
INNER JOIN book_rfid_rel ON book_rfid_rel.id = rfid_rel_id
LEFT JOIN admin issue_admin ON (book_borrow.issue_user_type = 'admin' AND issue_admin.id = book_borrow.issue_user_id)
LEFT JOIN user issue_user ON (book_borrow.issue_user_type = 'user' AND issue_user.id = book_borrow.issue_user_id)
LEFT JOIN admin return_admin ON (book_borrow.return_user_type = 'admin' AND return_admin.id = book_borrow.return_user_id)
LEFT JOIN user return_user ON (book_borrow.return_user_type = 'user' AND return_user.id = book_borrow.return_user_id) WHERE ";

  $bind_params = [];

  if (!empty($all) && $all == true) {
    if ($returned == 1 || $returned == 0) {
      $sql .= " returned = :returned";
      $bind_params[":returned"] = $returned;
    }
  } else {
    if (!empty($id)) {
      $sql .= "book_borrow.id = :id AND";
      $bind_params[':id'] = $id;
    } else {
      if (!empty($user_id)) {
        $sql .= "book_borrow.user_id = :user_id AND ";
        $bind_params[":user_id"] = $user_id;
      }

      if (!empty($book_id)) {
        $sql .= "book_borrow.book_id = :book_id AND ";
        $bind_params[":book_id"] = $book_id;
      }

      // if (!empty($rfid_id)) {
      //   $sql .= "rfid_rel_id = :rfid_rel_id AND ";
      //   $bind_params[":rfid_rel_id"] = $rfid_id;
      // }


      if (!empty($borrow_date) && !empty($borrow_date_range)) {

        $borrow_date = new DateTime($borrow_date);
        $borrow_date->setTime(0, 0, 0);
        $borrow_date = $borrow_date->format('Y-m-d H:i:s');

        $borrow_date_range = new DateTime($borrow_date_range);
        $borrow_date_range->setTime(23, 59, 59);
        $borrow_date_range = $borrow_date_range->format('Y-m-d H:i:s');

        $sql .= "borrow_time BETWEEN :borrow_date AND :borrow_date_range AND ";
        $bind_params[":borrow_date"] = $borrow_date;
        $bind_params[":borrow_date_range"] = $borrow_date_range;
      }

      if ($returned == 0 || $returned == 1) {
        $sql .= "returned = :returned";
        $bind_params[":returned"] = $returned;
      }
    }
  }

  $sql = rtrim($sql, "AND ");
  $stmt =  $conn->prepare($sql);

  foreach ($bind_params as $key => $value) {
    $stmt->bindValue($key, $value);
  }

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    response(['data' => $data], 200);
  } else {
    response(['message' => 'Not Found'], 404);
  }

  if (!empty($book_id));
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
