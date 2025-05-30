# mini_product_inventory_app_full_stack
Building a small full-stack web app to manage products in inventory, built with Laravel (API + Blade), MySQL, and Bootstrap.  Supports user authentication (JWT), product CRUD, and responsive UI.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Getting Started](#getting-started)
  - [1. Clone the Repository](#1-clone-the-repository)
  - [2. Install Dependencies](#2-install-dependencies)
  - [3. Environment Configuration](#3-environment-configuration)
  - [4. Database Setup](#4-database-setup)
  - [5. Run the Application](#5-run-the-application)
- [API Endpoints](#api-endpoints)
  - [Authentication](#authentication)
  - [Products](#products)
- [Deployment (Local Simulation)](#deployment-local-simulation)
- [Future Enhancements (Bonus Points)](#future-enhancements-bonus-points)

## Project Overview

This application allows registered users to log in and manage a list of products, including adding new products, viewing existing ones, updating their details (name, price, quantity, status), and deleting them from the inventory.

## Features

*   User registration and login (JWT-based authentication).
*   Secure password hashing.
*   CRUD operations for products:
    *   Create new products.
    *   Read (list all products and view a single product).
    *   Update existing product details.
    *   Delete products.
*   Product attributes: Name, Price, Quantity, Status (in stock / out of stock).
*   Frontend interface built with Bootstrap and Blade templates.
*   RESTful API backend.

## Tech Stack

*   **Backend:** Laravel (PHP Framework)
*   **Frontend:** HTML, Bootstrap 5, JavaScript (Vanilla JS with Blade templating)
*   **Database:** MySQL
*   **Authentication:** JWT (`tymon/jwt-auth` package)
*   **PHP Version:** 8.1+ (Specify the version you used, e.g., PHP 8.1.10)
*   **Node.js Version:** 18.x or 20.x (Specify the version you used, e.g., Node.js v18.17.0)
*   **Composer**
*   **NPM**

## Prerequisites

Before you begin, ensure you have the following installed on your system:

*   PHP (version specified above)
*   Composer ([https://getcomposer.org/](https://getcomposer.org/))
*   Node.js and npm (versions specified above) ([https://nodejs.org/](https://nodejs.org/))
*   MySQL Server
*   A code editor (e.g., VS Code)
*   An API client (e.g., Postman, Insomnia) for testing API endpoints directly.
*   Git (optional, for version control)

## Getting Started

Follow these steps to get the project up and running on your local machine.

### 1. Clone the Repository

```bash
# If you have it on GitHub/GitLab:
# git clone <your-repository-url>
# cd mini_product_inventory_app_full_stack

# If you are just setting it up from your local files, ensure you are in the project root:
# cd C:\wamp64\www\mini_product_inventory_app_full_stack
```

### 2. Install Dependencies

Install backend (PHP) and frontend (Node.js) dependencies:

```bash
composer install
npm install
```

### 3. Environment Configuration

*   Copy the `.env.example` file to a new file named `.env`:
    ```bash
    cp .env.example .env
    ```
    (On Windows, you can use `copy .env.example .env`)
*   Open the `.env` file and configure your database connection and other settings:
    ```dotenv
    APP_NAME="Mini Product Inventory"
    APP_ENV=local
    APP_KEY= # This will be generated in the next step
    APP_DEBUG=true
    APP_URL=http://localhost:8000 # Or your WAMP/MAMP URL if not using `php artisan serve`

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=inventory_app_db # Or your chosen database name
    DB_USERNAME=root             # Your MySQL username
    DB_PASSWORD=                 # Your MySQL password (leave empty if none)

    JWT_SECRET= # This will be generated later
    ```
*   Generate the application key:
    ```bash
    php artisan key:generate
    ```
*   Generate the JWT secret key:
    ```bash
    php artisan jwt:secret
    ```

### 4. Database Setup

*   Ensure your MySQL server is running.
*   Create a new database in MySQL (e.g., using phpMyAdmin or a MySQL client) with the name you specified in `DB_DATABASE` (e.g., `inventory_app_db`).
*   Run the database migrations to create the necessary tables:
    ```bash
    php artisan migrate
    ```
    *Note: If you encounter a "Specified key was too long" error during migration, ensure `Schema::defaultStringLength(191);` is set in the `boot()` method of your `app/Providers/AppServiceProvider.php` file.*

### 5. Run the Application

*   **Start the Laravel development server (for the backend API and serving Blade views):**
    Open a terminal in the project root and run:
    ```bash
    php artisan serve
    ```
    This will typically start the server at `http://127.0.0.1:8000`.

*   **Start the Vite development server (for compiling frontend assets):**
    Open a *separate* terminal in the project root and run:
    ```bash
    npm run dev
    ```
    Keep this running while you are developing the frontend.

*   Access the application in your browser:
    *   Login page: `http://127.0.0.1:8000/login`
    *   Register page: `http://127.0.0.1:8000/register`

## API Endpoints

The application exposes the following RESTful API endpoints. All product endpoints and user profile/logout require JWT authentication (send `Authorization: Bearer <token>` header).

### Authentication

*   `POST /api/register` - Register a new user.
    *   Body: `name`, `email`, `password`, `password_confirmation`
*   `POST /api/login` - Login an existing user.
    *   Body: `email`, `password`
    *   Returns: `access_token`, `token_type`, `expires_in`, `user`
*   `POST /api/logout` - Logout the current user (requires token).
*   `GET /api/user-profile` - Get details of the authenticated user (requires token).
*   `POST /api/refresh` - Refresh an expired JWT (requires valid refresh token capability, if configured).

### Products

*   `GET /api/products` - List all products.
*   `POST /api/products` - Create a new product.
    *   Body: `name` (string, required), `price` (numeric, required), `quantity` (integer, required), `status` (string, optional, default: 'in stock')
*   `GET /api/products/{id}` - Get a single product by ID.
*   `PUT /api/products/{id}` - Update an existing product.
    *   Body: (same fields as create, all optional for update)
*   `DELETE /api/products/{id}` - Delete a product.

## Deployment (Local Simulation)

*(This section will be filled if Docker setup is implemented)*
Currently, the application is run using `php artisan serve` for the backend and `npm run dev` for frontend assets.
