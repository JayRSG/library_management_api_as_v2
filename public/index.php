<?php

session_start();
define('root', '../..');
define('app',  root . '/app');

require_once __DIR__ . '../../vendor/autoload.php';
require_once __DIR__ . "../../lib/utils.php";

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  // Enable CORS
  response("", 204, [
    'Access-Control-Allow-Origin' => 'http://libraryman.com',
    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE',
    'Access-Control-Allow-Headers' => 'Content-Type',
    'Access-Control-Allow-Credentials' => 'true'
  ]);
  return;
}


// Load the .env file for accessing client secrets
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

// Get the config information
require_once __DIR__ . "../../config/config.php";

// Get the request method GET, POST, PUT, DELETE
$request = $_SERVER['REQUEST_URI'];

// Extract the path from the request URI -> /, /books, /user etc.
$path = parse_url($request, PHP_URL_PATH);

// Extract the query string from the request URI  -> /user?param=value => param is the query
$queryString = parse_url($request, PHP_URL_QUERY);
parse_str($queryString, $queryParams);

// Define the http routes 
$routes = [
  "/"                                 =>       app . "/home.php",
  "/books/show"                       =>       app . "/books/show_books.php",
  "/books/add"                        =>       app . "/books/add_book.php",
  "/books/add_book_stock"             =>       app . "/books/add_book_stock.php",
  "/books/get_book_rfids"             =>       app . "/books/get_book_rfids.php",
  "/books/update"                     =>       app . "/books/update_book.php",
  "/books/update_book_stock"          =>       app . "/books/update_book_stock.php",
  "/books/delete"                     =>       app . "/books/delete_book.php",
  "/books/isbn"                       =>       app . "/books/valid_isbns.php",
  "/books/borrow_book"                =>       app . "/ancillary/borrow_book.php",
  "/books/reissue_book"               =>       app . "/ancillary/reissue_book.php",
  "/books/borrow_list"                =>       app . "/ancillary/borrow_list.php",
  "/books/search_borrow_info"         =>       app . "/ancillary/search_borrowed_book.php",
  "/books/return_book"                =>       app . "/ancillary/return_book.php",
  "/books/calculate_fine"             =>       app . "/ancillary/calculate_fine.php",
  "/books/pay_fine"                   =>       app . "/ancillary/pay_fine.php",
  "/sensor"                           =>       app . "/sensor.php",
  "/login"                            =>       app . "/login.php",
  "/logout"                           =>       app . "/logout.php",
  "/user/register"                    =>       app . "/user/register.php",
  "/user"                             =>       app . "/user/retrieve.php",
  "/user/update"                      =>       app . "/user/update.php",
  "/user/update_fingerprint"          =>       app . "/user/update_fingerprint.php",
  "/user/fingerprint_manage"          =>       app . "/user/fingerprint_manage.php",
  "/user/delete"                      =>       app . "/user/delete.php",
  "/admin/register"                   =>       app . "/admin/register.php",
  "/admin"                            =>       app . "/admin/retrieve.php",
  "/admin/users"                      =>       app . "/admin/users.php",
  "/admin/deleteUser"                 =>       app . "/admin/delete.php",
  "/admin/update"                     =>       app . "/admin/update.php",
];

// Check if the requested route exists in the routes array
if (array_key_exists($path, $routes)) {
  $file = __DIR__ . $routes[$path];

  // Pass the query parameters to the file using $_GET
  $_GET = $queryParams;

  // include file according to the route

  require_once $file;
} else {
  // if the route not found return a 404 message
  response(['message' => "Route Not Found"], 404);
}
