<?php

function validate($data)
{
  $expected_keys = ['id'];
  return expect_keys($data, $expected_keys);
}

if (!checkPostMethod()) {
  return;
}

$user = auth();
if (!$user) {
  response(['message' => 'Unauthenticated'], 401);
  return;
}

if (!checkUserType('admin')) {
  return;
}

if (!validate($_POST)) {
  response(['data' => 'Bad Request'], 400);
  return;
}

try {
  $id = $_POST['id']; //borrow_id

  $reissueTime = date("Y-m-d H:i:s"); // This gives you a Unix timestamp (integer)
  $dueTimestamp = time() + (7 * 24 * 60 * 60);
  $dueTime = date("Y-m-d H:i:s", $dueTimestamp);

  $sql = "SELECT * from book_borrow where  id = :id OR returned = 0 LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":id", $id);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // response(['data' => $data], 200);
    // return;

    if ($data[0]['reissue_time'] != null) {
      response(['message' => "Reissue limit over"], 200);
      return;
    }

    if ($data[0]['returned'] == 1) {
      response(['message' => "Can't reissue returned book"], 200);
      return;
    }


    $fine_data = calculate_fine($conn, $data[0]['user_id'], $id);
    // $find_data[0] -> fine_info
    // $find_data[1] -> borrow_info


    if (
      (isset($fine_data[0]['fine']) && !empty($fine_data[1]['fine_excused']) && ($fine_data[1]['fine_excused'] != 1 || empty($fine_data[1]['fine_excused']))) ||
      (!empty($fine_data[0]['fine']) && !empty($fine_data[1]['late_fine']) && $fine_data[0]['fine'] - $fine_data[1]['late_fine'] > 0)
    ) {
      response(['message' => "Must pay late fine before reissuing book"], 200);
      return;
    }
  }

  $sql = "UPDATE book_borrow SET reissue_time = :reissueTime, due_time = :due_time WHERE id = :id";

  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":reissueTime", $reissueTime);
  $stmt->bindParam(":due_time", $dueTime);
  $stmt->bindParam(":id", $id);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => "Book Reissued"], 200);
  } else {
    response(['message' => "Book reissue failed"], 422);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
