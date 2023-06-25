# Project Name
Library Management API - AS V2

[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

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

Copy the .env.example file contents into a new .env file. Replace the content if any. Provide the DATABASE, HOSTNAME, USERNAME, PASSWORD associated with your development environment. A database needs to be created by the name of your database, the schema is stored in the databbase folder (check if you find it, otherwise build one yourself silently reading the code and understand what it is expected to be like)


## Contributing

This is a private project in a public repo. You are free to contribute for yourself. Fork it. Build something with it.

## License

No licensing. Just feel free to use it, change it, modify it, redistribute it.

## Acknowledgements

I'd like to thank myself for building this project.

## Contact

Thanks for not contacting.

