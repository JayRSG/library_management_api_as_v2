<?php

/**
 * Get users
 */

use function PHPSTORM_META\map;

if (!checkGetMethod()) {
  return;
}

if (!checkUserType("admin")) {
  return;
}

try {
  $id = !empty($_GET['id']) ? $_GET['id'] : null;
  $student_id = !empty($_GET['student_id']) ? $_GET['student_id'] : null;
  $email = !empty($_GET['email']) ? $_GET['email'] : null;
  $phone = !empty($_GET['phone']) ? $_GET['phone'] : null;
  $active = !empty($_GET['active']) ? $_GET['active'] : null;
  $deleted = !empty($_GET['deleted']) ? $_GET['deleted'] : null;

  $account_type = !empty($_GET['account_type']) ? $_GET['account_type'] : null;

  if ($deleted && $account_type == "admin") {
    response(['message' => 'Bad request'], 400);
    return;
  }

  if ($active && $account_type == "user") {
    response(['message' => 'Bad request'], 400);
    return;
  }


  if (!$account_type && ($account_type != "user" && $account_type != "admin")) {
    response(['message' => 'Bad Request'], 400);
    return;
  }

  $selectable_columns =
    $account_type == "user" ? ['user.id, first_name, last_name, fingerprint_id, email, phone, student_id, user_type_id, user_type, deleted'] : ($account_type == "admin" ?
      ['admin.id, first_name, last_name, email, fingerprint_id, active'] : null);

  if (!$selectable_columns) {
    response(['message' => 'Bad Request'], 400);
    return;
  }

  $sql = "SELECT ";
  foreach ($selectable_columns as $value) {
    $sql .= "$value, ";
  }
  $sql = rtrim($sql, ", ");
  $join_stmt = $account_type == "user" ? "LEFT JOIN user_type on user_type_id = user_type.id" : '';
  $sql .= " FROM $account_type $join_stmt WHERE";

  $params = array();

  if ($id) {
    $sql .= " $account_type.id = :id AND";
    $params[':id'] = $id;
  }

  if ($student_id) {
    $sql .= " student_id = :student_id AND";
    $params[':student_id'] = $student_id;
  }

  if ($email) {
    $sql .= " email = :email AND";
    $params[':email'] = $email;
  }

  if ($phone) {
    $sql .= " phone = :phone AND";
    $params[':phone'] = $phone;
  }

  if ($deleted) {
    $sql .= " deleted = :deleted AND";
    $params[':deleted'] = $deleted;
  }

  if ($active) {
    $sql .= " active = :active AND";
    $params[':active'] = $active;
  }


  $sql = rtrim($sql, " WHERE");
  $sql = rtrim($sql, " AND");

  $stmt = $conn->prepare($sql);

  foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
  }

  $result = $stmt->execute();
  if ($result && $stmt->rowCount() > 0) {

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($users) {
      $i = 0;
      foreach ($users as $value) {
        foreach ($value as $student_info_key => $student_info_value) {
          if ($student_info_key == "student_id" && $student_info_value != '') {
            $student_info = extractStudentInfo($student_info_value);
            $users[$i++]['student_info'] = $student_info;
          }
        }
      }

      response(["data" => $users], 200);
    } else {
      response(["message" => "Users not Found"], 404);
    }
  } else {
    response(["message" => "No Users Found"], 404);
  }
} catch (PDOException $e) {
  response(['message' => $e->getMessage()], 500);
}
