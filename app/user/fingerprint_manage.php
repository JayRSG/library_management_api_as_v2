<?php

function validate($data)
{
  return expect_keys($data, ['operation']);
}

if (!checkGetMethod()) {
  return;
}

if (!checkUserType('admin')) {
  return;
}


try {
  $operation = $_GET['operation'];
  $fingerprint_data = $_GET['fingerprint'] ?? null;

  if ($operation == "search") {
    $sql = "SELECT
    'user' AS account_type,
    user.id  
FROM
    user
JOIN
    fingerprints ON user.fingerprint_id = fingerprints.id
WHERE
    fingerprints.fingerprint = :fingerprint

UNION

SELECT
    'admin' AS account_type,
    admin.id 
FROM
    admin
JOIN
    fingerprints ON admin.fingerprint_id = fingerprints.id
WHERE
    fingerprints.fingerprint = :fingerprint LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":fingerprint", $fingerprint_data);

    $result = $stmt->execute();

    if ($result && $stmt->rowCount() > 0) {
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];

      if ($data) {
        response(['data' => $data]);
      } else {
        response(['message' => 'Not Found'], 404);
      }
    }
  } else if ($operation == "retrieve") {
    /** Retrieve the next autoincrement id for storing fingerprint */
    $sql = "SELECT AUTO_INCREMENT as AI
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = 'library_management_as'
    AND TABLE_NAME = 'fingerprints'";

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute();

    if ($result && $stmt->rowCount() > 0) {
      $fingerprint = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['AI'];

      response(['data' => $fingerprint], 200);
    } else {
      response(['message' => "Not Found"], 404);
    }
  } else if ($operation == "store") {
    /** Store new fingerprint ID in the table */

    if (!$fingerprint_data) {
      response(['message' => "Bad Request"], 400);
      return;
    }

    $sql = "INSERT INTO fingerprints (fingerprint) VALUES (:fingerprint)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":fingerprint", $fingerprint_data);

    $result = $stmt->execute();

    if ($result && $stmt->rowCount() > 0) {
      $id = $conn->lastInsertId();

      if ($id) {
        response(['data' => $id]);
      } else {
        response(['message' => 'Failed Insertion'], 404);
      }
    }
  } else if ($operation == "revert") {
    /** Delete fingerprint ID from the table */
    $id = $_GET['id'] ?? null;
    if ($id = null) {
      response(['message' => 'Bad Request'], 400);
      return;
    }

    $sql = "DELETE from fingerprints where id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);

    $result = $stmt->execute();

    if ($result && $stmt->rowCount() > 0) {
      response(['message' => "Delted fingerprint $id"]);
    } else {
      response(['message' => "Failed delete operation"]);
    }
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
