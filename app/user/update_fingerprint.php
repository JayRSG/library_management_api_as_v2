<?php

function validate($data)
{
  return expect_keys($data, ['fingerprint_id', 'account_type', 'user_id']);
}

if (!checkPostMethod()) {
  return;
}

if (!checkUserType('admin')) {
  return;
}

if (!validate($_POST)) {
  response(['message' => "Bad Request"], 400);
  return;
}

try {
  $user_id = $_POST['user_id'];
  $fingerprint = $_POST['fingerprint_id'];
  $account_type = $_POST['account_type'];

  $sql = "UPDATE $account_type SET fingerprint_id = :fingerprint_id where id = :id";

  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":fingerprint_id", $fingerprint);
  $stmt->bindParam(":id", $user_id);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => 'Fingerprint updated']);
  } else {
    response(['message' => 'Fingerprint Update Failed'], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
