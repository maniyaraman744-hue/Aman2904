# ☁️ AWS EC2 + RDS PHP Student Registration Application

A PHP student registration application hosted on an Ubuntu Amazon EC2 instance and connected to an Amazon RDS MySQL database in a private subnet.

> **Key principle:** The application is publicly reachable, but the database is not directly exposed to the internet.

## 📌 Problem Statement

Build a simple PHP application that collects student information through a browser and stores it in MySQL, while keeping the database private.

**Goal:** Public web tier on EC2 + private database tier on RDS.

## 🏗️ Architecture

```text
                         INTERNET
                            |
                            v
                    Internet Gateway
                            |
                            v
              ┌────────────────────────────┐
              │           VPC              │
              │                            │
              │  ┌──────────────────────┐  │
              │  │ EC2                  │  │
              │  │ Public Subnet        │  │
              │  │ Ubuntu               │  │
              │  │ Apache + PHP         │  │
              │  └──────────┬───────────┘  │
              │             │              │
              │        MySQL : 3306        │
              │             │              │
              │             v              │
              │  ┌──────────────────────┐  │
              │  │ Amazon RDS MySQL     │  │
              │  │ Private Subnet       │  │
              │  │ studentdb            │  │
              │  │ students             │  │
              │  └──────────────────────┘  │
              └────────────────────────────┘
```

**Traffic path:** `User → EC2 → RDS`

## 🧰 Services Used

| Service | Purpose |
|---|---|
| **Amazon VPC** | Creates the isolated network for the project. |
| **Public Subnet** | Hosts EC2 because the web application must be reachable. |
| **Private Subnet** | Hosts RDS so the database is not directly exposed to the internet. |
| **Internet Gateway** | Provides internet connectivity for the public subnet. |
| **NAT Gateway** | Provides outbound internet access to private resources when required. |
| **Amazon EC2** | Runs Ubuntu, Apache, PHP, and the application. |
| **Amazon RDS for MySQL** | Provides the managed MySQL database. |
| **EC2 Security Group** | Controls traffic reaching the web server. |
| **RDS Security Group** | Controls MySQL access; allow port 3306 from the EC2 security group. |
| **Apache2** | Serves the PHP application. |
| **PHP** | Processes the application and communicates with MySQL. |
| **MySQL Client** | Tests the RDS connection from EC2. |
| **MobaXterm** | Provides terminal access to EC2. |

## ⚙️ Setup Steps

### 1. VPC & Subnets

Create a VPC with a public subnet and private subnet. Attach an Internet Gateway to the VPC and use a NAT Gateway when private resources require outbound internet access.

**Why?** The VPC separates the application network from other networks, while public/private subnets separate the web and database tiers.

### 2. EC2 Instance

Launch Ubuntu EC2 in the public subnet.

**Why?** EC2 runs the web server and PHP application that users access through the EC2 public IP.

### 3. RDS MySQL

Create RDS MySQL in the private subnet.

**Why?** RDS stores the application data and should not be directly reachable from the public internet.

### 4. Security Groups

EC2 should allow required web traffic such as HTTP port 80.

RDS should allow MySQL port 3306 **from the EC2 security group**, rather than from the whole internet.

### 5. Install Software on EC2

```bash
sudo apt update
sudo apt install apache2 -y
sudo apt install php php-mysql libapache2-mod-php -y
sudo apt install mysql-client -y
sudo systemctl restart apache2
```

### 6. Connect to RDS

```bash
mysql -h YOUR_RDS_ENDPOINT -u admin -p
```

Create the database:

```sql
CREATE DATABASE studentdb;
USE studentdb;

CREATE TABLE students(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100)
);
```

## 🌐 PHP Application

Apache serves files from:

```text
/var/www/html
```

```bash
cd /var/www/html
```

### 1. index.php

**Purpose:** Main page. Displays the registration form and sends the submitted data to `save.php`.

```php
<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
</head>
<body>

<h2>Student Registration Form</h2>

<form action="save.php" method="POST">
    <label>Name:</label>
    <input type="text" name="name" required>

    <br><br>

    <label>Email:</label>
    <input type="email" name="email" required>

    <br><br>

    <input type="submit" value="Register">
</form>

</body>
</html>
```

### 2. connect.php

**Purpose:** Creates the PHP-to-RDS MySQL connection.

```php
<?php

$host = "YOUR_RDS_ENDPOINT";
$user = "admin";
$password = "YOUR_RDS_PASSWORD";
$db = "studentdb";

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Connection Failed");
}

?>
```

### 3. save.php

**Purpose:** Receives form data and inserts it into the RDS `students` table.

```php
<?php

include "connect.php";

$name = $_POST['name'];
$email = $_POST['email'];

$sql = "INSERT INTO students(name, email)
        VALUES('$name', '$email')";

if (mysqli_query($conn, $sql)) {
    echo "<h2>Student Registered Successfully</h2>";
} else {
    echo "Error: " . mysqli_error($conn);
}

?>
```

## 🔄 Application Flow

```text
Browser
   |
   v
index.php
   |
   | POST name + email
   v
save.php
   |
   | include
   v
connect.php
   |
   | MySQL : 3306
   v
Amazon RDS
   |
   v
studentdb.students
```

## 🧪 Testing

Open:

```text
http://YOUR_EC2_PUBLIC_IP
```

Submit a student record.

Then verify:

```bash
mysql -h YOUR_RDS_ENDPOINT -u admin -p
```

```sql
USE studentdb;
SELECT * FROM students;
```

## 🔐 Security Design

Users access:

```text
Internet → EC2 → PHP Application
```

Users should not access:

```text
Internet → RDS
```

Recommended RDS rule:

```text
Type: MySQL/Aurora
Port: 3306
Source: EC2 Security Group
```

Avoid using `0.0.0.0/0` as the RDS MySQL source.

## 🧠 Key Learnings

- VPC provides network isolation.
- Public subnet is used for the internet-facing web tier.
- Private subnet is used for the database tier.
- EC2 runs the application.
- Apache serves PHP.
- PHP connects to RDS.
- Security groups control EC2-to-RDS traffic.
- RDS stores the application data.

## 🧹 Cleanup

After testing, remove resources you no longer need to avoid unnecessary AWS charges, especially RDS and NAT Gateway resources.

## ⚠️ GitHub Security

Never upload:

- RDS passwords
- AWS access keys
- Secret keys
- `.pem` files
- `.env` files containing secrets

Use placeholders in GitHub:

```php
$host = "YOUR_RDS_ENDPOINT";
$password = "YOUR_RDS_PASSWORD";
```

## 📁 Repository Structure

```text
AWS-EC2-RDS-PHP-Application/
├── README.md
├── project.json
├── index.php
├── connect.php
├── save.php
└── .gitignore
```

## 📄 Result

The PHP application is hosted on EC2 and stores student records in Amazon RDS MySQL located in a private subnet.

## 👨‍💻 Author

**Aman Maniyar**

BCA Graduate | AWS / Cloud & DevOps Fresher
