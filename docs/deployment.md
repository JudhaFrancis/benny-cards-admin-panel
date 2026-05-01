# Production Deployment

Follow these steps to deploy the Scan Center Management Admin Panel to a production server (Ubuntu/Nginx/Apache).

## Server Requirements

- PHP >= 8.1
- MySQL >= 5.7
- Nginx or Apache
- Composer
- Node.js (for building assets)

## Environment Setup

### 1. Preparing the Server
Ensure your server has the necessary PHP extensions: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pcre`, `tokenizer`, `xml`.

### 2. Uploading Project
Upload your project via SSH or Git.
```bash
git clone https://github.com/your-repo.git /var/www/admin-panel
```

### 3. Composer Installation
Install dependencies optimized for production.
```bash
composer install --optimize-autoloader --no-dev
```

### 4. Build Frontend Assets
Build assets locally and upload, or build directly on the server.
```bash
pnpm install
pnpm run build
```

### 5. Configuration
Configure `.env` for production:
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Update `APP_URL` to your production domain.
- Update Production Database credentials.

### 6. Security and Optimization
Initialize the application cache and link storage.
```bash
php artisan key:generate
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. File Permissions
Ensure the web server (e.g., `www-data`) has ownership of the directories.
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

## Web Server Configuration

### Nginx Example
```nginx
server {
    listen 80;
    server_name admin.yourdomain.com;
    root /var/www/admin-panel/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## Queue Setup (If required)
If the application uses background jobs (for emails or reports), configure a process monitor like **Supervisor** to run the queue worker.
```bash
php artisan queue:work --tries=3
```

## Security Best Practices

- **HTTPS:** Always serve the site over SSL (e.g., via Certbot/Let's Encrypt).
- **Environment Restricted:** Ensure `.env` is never accessible via the browser.
- **Database Backup:** Schedule regular database dumps.
- **App Debug:** Verify `APP_DEBUG` is `false` to prevent sensitive data leaks in errors.

---
[Return to Home](../README.md)
