<?php

require_once __DIR__ . '../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

$request = $_SERVER['REQUEST_URI'];
// Extract the path from the request URI
$path = parse_url($request, PHP_URL_PATH);

// Extract the query string from the request URI
$queryString = parse_url($request, PHP_URL_QUERY);
parse_str($queryString, $queryParams);


$routes = [
  "/" => "../../app/home.php",
  "/books" => "../../app/books.php",
  "/book_barcode" => "../../app/book_barcode.php",
  "/borrow_book" => "../../app/borrow_book.php",
  "/sensor" => "../../app/sensor.php",
  "/user" => "../../app/user.php",
];


// Check if the requested route exists in the map
if (array_key_exists($path, $routes)) {
  $file = __DIR__ . $routes[$path];

  // Pass the query parameters to the file using $_GET
  $_GET = $queryParams;

  require $file;
} else {
  http_response_code(404);
  require __DIR__ . '/../app/error404.php';
}