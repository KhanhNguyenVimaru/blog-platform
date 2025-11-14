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
For a fully detailed walkthrough see `docs/setup.txt`. The short version:
1. Clone the repo and install dependencies (`composer install`, `npm install`).
2. Copy `.env.example` to `.env`, configure database/mail credentials, then run `php artisan key:generate`.
3. Create the database, run `php artisan migrate --seed` (or the seeders you need), and install Passport with `php artisan passport:client --personal`.
4. Build assets via `npm run build` (or `npm run dev` for hot reload), link storage (`php artisan storage:link`), and start the server with `php artisan serve`.

If you get stuck, the setup doc includes troubleshooting notes plus queue/scheduler commands.
