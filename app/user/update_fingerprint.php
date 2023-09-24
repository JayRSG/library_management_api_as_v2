<?php

function validate($data)
{
  return expect_keys($data, ['fingerprint', 'user_id']);
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
  $fingerprint = $_POST['fingerprint'];

  $sql = "UPDATE user SET fingerprint = :fingerprint where id = :id";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":fingerprint", $fingerprint);
  $stmt->bindParam(":id", $user_id);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => 'Finger print updated']);
  } else {
    response(['message' => 'Update Failed'], 400);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()]);
}
