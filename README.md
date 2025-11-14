# Digital Blog

**Digital Blog** is a blogging platform that allows users to easily create, share, and interact with posts.  
Built with **Laravel** for the backend, **Tailwind CSS** for the UI, **MySQL** for the database, and **JavaScript** (Cropper.js, SweetAlert2, Editor.js) to enhance user experience.

## Main Features
- **Account**: Register, login, email verification, change password, update profile, update avatar, delete account.
- **Posts**: Create, update status, delete, upload files, view posts by category or author, fetch all posts via API.
- **Comments & Likes**: Add/remove comments, like/unlike posts, count likes.
- **Follow System**: Follow/unfollow users, accept/deny follow requests, ban/unban users, view followers/following lists.
- **Notifications & Search**: Receive/delete notifications, search suggestions, advanced search, preview links when creating posts.
- **Security**: `auth` middleware protects important routes.

## System Requirements
- PHP 8.x, Laravel 12.x
- MySQL 8.x
- Node.js & npm (for asset building)

## Local Setup
Digital Blog - Local Setup
==========================

Prerequisites
-------------
- PHP 8.2+, Composer 2
- MySQL 8 (or MariaDB equivalent)
- Node.js 18+ and npm
- Git and a working queue/cron solution (Supervisor, systemd) if you plan to run queues

1. Clone the repository
-----------------------
```bash
git clone https://github.com/KhanhNguyenVimaru/blog-platform.git
cd blog-platform
```

2. Install PHP dependencies
---------------------------
```bash
composer install
```

3. Configure the environment
----------------------------
- Duplicate `.env.example` to `.env`.
- Set `APP_URL`, mail credentials, database name/user/password and any third-party keys.
- Generate the app key:
  ```bash
  php artisan key:generate
  ```

4. Prepare the database
-----------------------
- Ensure the database configured in `.env` exists.
- Run the migrations (fresh install):
  ```bash
  php artisan migrate
  ```
- Seed base data if needed (example):
  ```bash
  php artisan db:seed
  ```

5. Configure Laravel Passport
-----------------------------
```bash
php artisan passport:client --personal
```
Save the generated client ID/secret for API requests.

6. Build frontend assets
------------------------
```bash
npm install
npm run build   # or "npm run dev" when developing
```

7. Link storage (one time)
--------------------------
```bash
php artisan storage:link
```

8. Serve the application
------------------------
```bash
php artisan serve
```
or configure your preferred web server (Nginx/Apache) to point to `public/`.

9. Optional services
--------------------
- Start the queue worker: `php artisan queue:work`.
- Schedule cron entry: `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`.

You now have a fully working local Digital Blog environment. Refer to the README for troubleshooting tips and additional context.
