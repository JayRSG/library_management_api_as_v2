<?php

function auth()
{
  if (isset($_SESSION['auth'])) {
    return $_SESSION['auth'];
  } else {
    return false;
  }
}

function checkPostMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "POST") {
    http_response_code(403);
    echo "Forbidden";
    return false;
  }
  return true;
}

function checkGetMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "GET") {
    http_response_code(403);
    echo "Forbidden";
    return false;
  }
  return true;
}

function checkPutMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "PUT") {
    http_response_code(403);
    echo "Forbidden";
    return false;
  }
  return true;
}

function checkDeleteMethod()
{
  if ($_SERVER['REQUEST_METHOD'] != "DELETE") {
    http_response_code(403);
    echo "Forbidden";
    return false;
  }
  return true;
}
