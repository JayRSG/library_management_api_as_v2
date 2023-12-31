# Project Name
Library Management API - AS V2

[![License](https://img.shields.io/badge/Licence-None-blue.svg)](LICENSE)

## Description

A library management api based on php which supports fingerprint sensor for login and rfid tags for identifying books. This custom, php application is written from scratch and only includes functionalities to accomodate a desktop application, an arduino hosting a fingerprint module and rfid reader. This simple project aims to enable storing data in a mysql database related to books and users of a library management system.

## Features

- Simple CRUD API interface.
- Support registering users
- Support registering books
- Supports Barcode for books
- Supports RFID tags for books
- Supports fingerprint sensors
 


## Usage

This project is used in conjunction with a frontend (mobile app/spa/desktop application). It basically provides the API endpoints to be consumed from the frontend. The frontend interfaces with an hardware powered by arduino including a fingerprint sensor and a rfid reader.

## Configuration

Copy the .env.example file contents into a new .env file. 
Replace the content if any. 
Provide the 

- DB_HOST=localhost
- DB_PORT=3306
- DB_DATABASE=
- DB_USERNAME=
- DB_PASSWORD=

associated with your project. 
A database needs to be created, the schema is stored in the databbase folder (check if you find it, otherwise build one yourself silently reading the code and understand what it is expected to be like)

## Run
Open a terminal, cd in to the project root and type in the below command to run this application
`php -S localhost:8000 -t public`

Alternatively, if you want to have custom hostnames for the api you can try--
`php -S customhostname:80 -t public`

Make sure you add the `customhostname` into your hosts file.
Check the internet to see how the hosts file can be edited.

## Contributing

This is a private project in a public repo. You are free to contribute for yourself. Fork it. Build something with it.

## License

No licensing. Just feel free to use it, change it, modify it, redistribute it.

## Acknowledgements
Thanks `AS` for inspiring me to build this project.

## Contact


