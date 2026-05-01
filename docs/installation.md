# Installation Guide

This guide will help you set up the Scan Center Management Admin Panel on your local machine.

## Prerequisites

Before you begin, ensure you have the following installed:
- **PHP** (>= 8.1)
- **Composer** (Latest Version)
- **Node.js & pnpm** (Latest LTS Version)
- **MySQL** (>= 5.7)
- **XAMPP / Laragon / Homebrew** (Local server environment)

## Setup Instructions

### 1. Clone the Repository
```bash
git clone https://github.com/JudhaFrancis/benny-cards-admin-panel.git
cd benny-cards-admin-panel
```

### 2. Install Backend Dependencies
```bash
composer install
```

### 3. Install Frontend Dependencies
```bash
pnpm install
```

### 4. Environment Configuration
Copy the example environment file and update your database credentials.
```bash
cp .env.example .env
```
Open `.env` and configure:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=benny_cards_db
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Database Setup & Migrations
Create the database in MySQL and run:
```bash
php artisan migrate --seed
```
*Note: The seeder will set up default roles and a super-admin user.*

### 7. Storage Link
Create a symbolic link from `public/storage` to `storage/app/public` to serve uploaded files.
```bash
php artisan storage:link
```

### 8. Run the Application

#### Start Backend Server
```bash
php artisan serve
```

#### Start Frontend Dev Server (Vite)
```bash
pnpm dev
```

The application will be accessible at `http://localhost:8000`.

## Common Installation Issues

- **Database Connection Refused:** Ensure MySQL is running and credentials in `.env` are correct.
- **Vite Build Errors:** Try deleting `node_modules` and `pnpm-lock.yaml`, then run `pnpm install` again.
- **Permission Denied (Storage):** Ensure the `storage` and `bootstrap/cache` directories are writable.
  ```bash
  chmod -R 775 storage bootstrap/cache
  ```

---
[Return to Architecture Overview](architecture.md)
