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
    response(['message' => "Unauthorized"], 401);
    return false;
  }
  return true;
}

function checkPostMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "POST") {
    response(['message' => "Forbidden"], 403);
    return false;
  }
  return true;
}

function checkGetMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "GET") {
    response(['message' => "Forbidden"], 403);
    return false;
  }
  return true;
}

function checkPutMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "PUT") {
    response(['message' => "Forbidden"], 403);
    return false;
  }
  return true;
}

function checkDeleteMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "DELETE") {
    response(['message' => "Forbidden"], 403);
    return false;
  }
  return true;
}

function response($response, $code = 200, $headers = null)
{

  if ($headers != null) {
    foreach ($headers as $name => $data) {
      header("$name: $data");
    }
  }

  http_response_code($code);
  echo json_encode($response);
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