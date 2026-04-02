# Uni-DMS — Local Setup Guide (XAMPP)

## Requirements
- XAMPP with MySQL 8.0+ and PHP 7.4+
- XAMPP Control Panel running Apache + MySQL

## 1. Start XAMPP
Open XAMPP Control Panel and click Start for both:
- Apache
- MySQL

## 2. Create the Database
Open your browser and go to: http://localhost/phpmyadmin
- Click the "Import" tab
- Choose file: `setup_local_db.sql`
- Click "Go"

## 3. Place Project Files
Copy the entire project folder into:
  C:\xampp\htdocs\uni-dms\

## 4. Verify .env Settings
Open `.env` and confirm these values match your XAMPP setup:
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_NAME=uni_dms
  DB_USER=root
  DB_PASSWORD=        ← must be empty for default XAMPP

## 5. Open the Website
Student portal: http://localhost/uni-dms/
Admin panel:    http://localhost/uni-dms/admin/login.php

## Default Admin Login
Username: admin
Password: admin123

## Troubleshooting

| Error | Fix |
|---|---|
| "Access denied for user root" | DB_PASSWORD in .env must be empty string, not "root" |
| "No connection could be made" | Start MySQL in XAMPP Control Panel |
| "Unknown database uni_dms" | Run setup_local_db.sql in phpMyAdmin first |
| "SSL connection error" | Confirm all SSL options removed from db.php |
| Page not found | Project must be in C:\xampp\htdocs\uni-dms\ |
| Port conflict | Another MySQL is running — stop it or change XAMPP MySQL port |
