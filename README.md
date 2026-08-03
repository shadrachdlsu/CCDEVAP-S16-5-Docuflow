# DocuFlow - Document Tracking & Management System

DocuFlow is a web-based document lifecycle and tracking system supporting role-based access for **Admin**, **Secretary**, and **Member** workflows.

---

## Prerequisites

Before running the application, ensure you have the following installed and running:

1. **XAMPP** (with **Apache** and **MySQL** modules enabled).

---

## Setup & Installation Instructions

### Step 1: Clone / Copy Project Location
Ensure the project directory is located inside your XAMPP web root directory under:
```text
C:\xampp\htdocs\
```

---

### Step 2: Database Initialization

1. Open **XAMPP Control Panel** and click **Start** for both **Apache** and **MySQL**.
2. Open **phpMyAdmin** in your browser: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
3. Import the database SQL script:
   - Select the **Import** tab.
   - Choose the file: [`CCDEVAP-S16-5-Docuflow-More.sql`](file:///c:/xampp/htdocs/CCDEVAP-S16-5-Docuflow/CCDEVAP-S16-5-Docuflow-More.sql) (or [`CCDEVAP-S16-5-Docuflow.sql`](file:///c:/xampp/htdocs/CCDEVAP-S16-5-Docuflow/CCDEVAP-S16-5-Docuflow.sql)).
   - Click **Import** to generate and populate the `docuflow_db` database.


---

### Step 3: Database Connection Configuration

Verify or edit your database connection credentials in [`config/connections.php`](file:///c:/xampp/htdocs/CCDEVAP-S16-5-Docuflow/config/connections.php):

```php
$host     = 'localhost';
$dbname   = 'docuflow_db';
$username = 'root';
$password = 'admin'; // Adjust to match your local MySQL password (default in XAMPP is empty '')
```

---

### Step 4: Running the Web Application

#### Via XAMPP Apache Server
1. Ensure Apache is running in XAMPP.
2. Open your browser and navigate to:
   ```text
   http://localhost/CCDEVAP-S16-5-Docuflow/
   ```
   *or directly to the login page:*
   ```text
   http://localhost/CCDEVAP-S16-5-Docuflow/views/login.php
   ```


## Test Credentials

For a complete list of pre-seeded accounts across Admin, Secretary, and Member roles, view [`CREDENTIALS.md`](file:///c:/xampp/htdocs/CCDEVAP-S16-5-Docuflow/CREDENTIALS.md).

* **Default Universal Password for all Seed Users:** `dlsu`
* **Admin Login:** `admin@docuflow.local` (Password: `dlsu`)
* **Secretary Login:** `secretary@docuflow.local` (Password: `dlsu`)
* **Member Login:** `juan.member@docuflow.local` (Password: `dlsu`)
