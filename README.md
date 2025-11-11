# Wake-on-LAN Manager (Web UI)

A modern **Web-based Wake-on-LAN / Remote Shutdown / SSH Console** dashboard built with PHP + MySQL + Bootstrap.

---

## ✅ Features

| Feature | Admin | User |
|--------|-------|------|
| Wake / Shutdown individual devices | ✅ | ✅ (permission-based) |
| Wake All / Shutdown All | ✅ | ✅ |
| Live status: Ping + SSH check | ✅ | ✅ |
| SSH Web Console (execute commands via browser) | ✅ | ✅ (permission-based) |
| Add / Edit / Delete devices | ✅ | ❌ |
| User management | ✅ | ❌ |
| Assign devices to users | ✅ | ❌ |
| Bulk device assignment | ✅ | ❌ |
| Audit logging | ✅ | ✅ (read only) |

---

## 📦 Requirements

| Component | Required |
|----------|----------|
| Debian / Ubuntu | ✅ |
| Apache2 + mod_rewrite | ✅ |
| PHP 8.1+ | ✅ |
| MariaDB / MySQL | ✅ |
| SSH / WOL utilities (`etherwake`, `sshpass`) | ✅ |

---

## 🚀 Installation (Debian / Ubuntu)

### 1️⃣ Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### 2️⃣ Install Dependencies
```bash
sudo apt install apache2 php php-mysqli mariadb-server sshpass etherwake net-tools git -y
```

### 3️⃣ Download Project
```bash
cd /var/www/html/
sudo git clone https://github.com/GeekInstitute/wol-manager.git
sudo chown -R www-data:www-data wol-manager
sudo chmod -R 755 wol-manager
sudo mkdir -p /var/www/.ssh
sudo chown -R www-data:www-data /var/www/.ssh
sudo chmod -R 755 /var/www/.ssh
```

### 4️⃣ Setup Database
```bash
sudo mysql
```

Inside MySQL shell:
```sql
CREATE DATABASE wol_manager;
USE wol_manager;
SOURCE /var/www/html/wol-manager/database.sql;
EXIT;
```

### 5️⃣ Configure Apache Virtual Host
```bash
sudo nano /etc/apache2/sites-available/wol-manager.conf
```

Paste:
```
<VirtualHost *:80>
    ServerName wol.local
    DocumentRoot /var/www/html/wol-manager

    <Directory /var/www/html/wol-manager>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Apply config:
```bash
sudo a2enmod rewrite
sudo a2ensite wol-manager
sudo systemctl restart apache2
```

---

## 🔑 First Login

Open browser:
```
http://YOUR-SERVER-IP/wol-manager/login.php
```

Default admin login:
| Username | Password |
|----------|----------|
| admin | admin123 |

If you forget password:
```bash
php reset_admin.php
```

⚠ **Important: delete reset_admin.php after use!**

---

## 📁 Project Structure

```
/wol-manager
│── actions/         → wake / shutdown / save / assign handlers
│── api/             → status (ping/ssh)
│── includes/        → auth, audit, utility functions
│── pages/           → dashboard / devices / console / users
│── assets/js/       → status polling script
│── database.sql     → database schema
│── config.php       → DB and application settings
│── reset_admin.php  → resets admin password
```

---

## 🔒 Security

- CSRF Protected
- Role / Permission based access
- Logs every activity (audit table)
- Secure Session Handling (SameSite + HttpOnly)

---

## 📜 MIT License

```
MIT License

Copyright (c) 2025 The Geek Institute of Cyber Security

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the “Software”), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 💬 Support

Created by: **The Geek Institute of Cyber Security**  
Website: https://www.geekinstitute.org
Email : info@geekinstitute.org
