<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


if (!checkPostMethod()) {
  return;
}

if (!checkUserType('admin')) {
  response(['message' => "Unauthorized"], 401);
  return;
}

try {
  $rfid = empty($_POST['rfid']) ?: $_POST['rfid'];
  $order_by = empty($_POST['order_by']) ? "" : $_POST['order_by'];

  if ($rfid) {
    $sql = "SELECT bb.*, user.first_name, user.last_name, user.student_id
    FROM book_borrow bb
    INNER JOIN book_rfid_rel brr ON bb.book_id = brr.book_id
    INNER JOIN book b ON brr.book_id = b.id
    INNER JOIN user ON bb.user_id = user.id
    WHERE brr.rfid = :rfid GROUP BY bb.borrow_time" . ($order_by != "" ? " " . $order_by : "");

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":rfid", $rfid);
    $result = $stmt->execute();

    if ($result && $stmt->rowCount() > 0) {
      $borrow_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

      response(['data' => $borrow_info], 200);
    }
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
