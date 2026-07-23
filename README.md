# Docuflow

Docuflow is a PHP and MariaDB document-routing system for administrators, office secretaries, and members. It supports PDF uploads, office routing, document status tracking, completion reports, and user management.

## Setup

1. Install and open XAMPP, then start **Apache** and **MySQL**.
2. Place the project folder at `C:\xampp\htdocs\docuflow`.
3. Open phpMyAdmin at `http://localhost/phpmyadmin` and create a database named `docuflow_db`.
4. Import `docuflow_db FINAL.sql` into `docuflow_db`.
5. Open `http://localhost/docuflow/views/login.php`.

The included SQL dump contains sample users, offices, documents, routes, and references to the PDFs in the `uploads` folder.
