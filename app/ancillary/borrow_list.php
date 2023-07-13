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
  $user_id = $_GET['user_id'] ?? null;
  $borrow_date = $_GET['date_from'] ?? null;
  $borrow_date_range = $_GET['date_to'] ?? null;
  $returned = isset($_GET['returned']) && $_GET['returned'] != "" && ($_GET['returned'] == 1 || $_GET['returned'] == 0) ? $_GET['returned'] : -1;

  $sql = "";
  $bind_params = [];

  if (!empty($all) && $all == true) {
    $sql = "SELECT * FROM book_borrow ";

    if ($returned == 1 || $returned == 0) {
      $sql .= "WHERE returned = :returned";
      $bind_params[":returned"] = $returned;
    }
  } else {
    $sql = "SELECT * from book_borrow WHERE ";

    if (!empty($id)) {
      $sql .= "id = :id AND";
      $bind_params[':id'] = $id;
    } else {
      if (!empty($user_id)) {
        $sql .= "user_id = :user_id AND ";
        $bind_params[":user_id"] = $user_id;
      }

      if (!empty($book_id)) {
        $sql .= "book_id = :book_id AND ";
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
