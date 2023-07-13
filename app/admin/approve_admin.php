<?php

function validateApproveInput($data)
{
  if (empty($data['id'])) {
    return false;
  } else {
    return true;
  }
}

if (!checkPostMethod()) {
  return;
}

if (!checkUserType("admin")) {
  return;
}

if (!validateApproveInput($_POST)) {
  response(['message' => 'Bad Request'], 400);
  return;
}

try {
  $id = $_POST['id'];

  $sql = "UPDATE admin SET active = 1 WHERE id = :id";
  $stmt = $conn->prepare($sql);

  $stmt->bindParam(":id", $id);

  $result = $stmt->execute();

  if ($result && $stmt->rowCount() > 0) {
    response(['message' => "Admin activated"], 200);
  } else {
    response(['message' => "Admin activation failed"], 404);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
