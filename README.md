# 🛍️ E-POS Shoes Store Admin

<p align="center">
  <img src="https://img.shields.io/badge/PHP-Native-blue?style=for-the-badge&logo=php">
  <img src="https://img.shields.io/badge/MySQL-Database-orange?style=for-the-badge&logo=mysql">
  <img src="https://img.shields.io/badge/Status-Active-success?style=for-the-badge">
  <img src="https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge">
</p>

<p align="center">
  A modern <b>Point of Sale (POS)</b> web application designed for managing a shoe store efficiently.<br>
  Built with simplicity, speed, and scalability in mind.
</p>

---

## ✨ Overview

**E-POS Shoes Store Admin** is a web-based POS system that helps store administrators handle:

- Product management  
- Sales transactions  
- Inventory tracking  
- Transaction history  

This project is ideal for **learning, prototyping, or small business use**.

---

## 📸 Preview

### 🖥️ Dashboard
<p align="center">
  <img src="preview/Dashboard.png" width="80%">
</p>

### 👟 Products
<p align="center">
  <img src="preview/Transactions.png" width="80%">
</p>

### 📋 Reports
<p align="center">
  <img src="preview/Reports.png" width="80%">
</p>

### 📦 Inventory
<p align="center">
  <img src="preview/Products.png" width="80%">
</p>

---

## 🚀 Features

- 🛍️ **Product Management**
  - Add, edit, delete products
  - Manage categories and stock

- 💳 **Transaction System**
  - Real-time purchase processing
  - Automatic price calculation

- 📊 **Admin Dashboard**
  - Sales overview
  - Stock monitoring

- 🧾 **Transaction History**
  - Detailed purchase records

- 🔐 **Authentication**
  - Secure admin login

---

## 🧰 Tech Stack
| Category   | Technology |
|------------|-----------|
| Backend    | PHP Native |
| Frontend   | HTML, CSS, JavaScript |
| Database   | MySQL |
| Server     | Apache (XAMPP / Laragon) |

---

## 📁 Project Structure
/admin # Admin dashboard
</br>
/assets # CSS, JS, images
</br>
/config # Database configuration
</br>
/proses # Core logic (CRUD, transactions)
</br>
/database # SQL files

---

## ⚙️ Installation Guide
### 1. Clone Repository
git clone https://github.com/BangJue/e-pos-shoes-store-admin.git
htdocs (XAMPP) / www (Laragon)

### 2. Move to Local Server
Place the project inside:
htdocs (XAMPP) / www (Laragon)

### 3. Setup Database
Open phpMyAdmin
Import .sql file from /database

### 4. Configure Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "your_database_name";

### 5. Run the Project
http://localhost/epos/login.php

**🔑 Default Login**
Username: admin
Password: admin
