<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


if (!auth()) {
  response(['message' => "Not Logged in"], 400);
  return;
} else {
  if (!checkPostMethod()) {
    return;
  }
}

if (isset($_POST['logout']) && $_POST['logout'] == true) {
  // logging out
  session_destroy();
  response([], 204);
}
