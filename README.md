# 👤 User Service

A microservice responsible for user management, authentication and authorization. Powered by **Laravel Passport** and **spatie/laravel-permissions**.

---

## 🔐 Authentication Setup

To set up authentication for this service, you need to configure Laravel Passport keys and clients. Follow the steps below.

### 1. Generate Encryption Keys

Run the following command to generate the RSA keys used to sign access tokens:

```bash
php artisan passport:keys

```

> **⚠️ Important Architecture Note:**
> This command generates both a **public** and a **private** key in your `storage/` directory.
> * **Private Key:** Keep this secret within this service.
> * **Public Key:** This must be shared with **API Gateway** to validate tokens issued by this service.
> 
> 

### 2. Create a Password Client

Create a client to handle password grant tokens (required for generating access and refresh tokens for standard login):

```bash
php artisan passport:client --password

```

## 📂 Filesystem Setup

To expose the public disk (for user avatars and public assets) to the web, you must create a symbolic link from `public/storage` to `storage/app/public`.

Run this command:

```bash
php artisan storage:link

```

---
