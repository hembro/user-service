# 👤 User Service

A microservice responsible for user management and authentication, powered by **Laravel Passport**.

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
> * **Public Key:** This must be shared with **every other microservice** that needs to validate tokens issued by this service.
> 
> 

### 2. Create a Password Client

Create a client to handle password grant tokens (required for generating access and refresh tokens):

```bash
php artisan passport:client --password

```

---
