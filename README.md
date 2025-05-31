# mini_product_inventory_app_full_stack
Building a small full-stack web app to manage products in inventory, built with Laravel (API + Blade), MySQL, and Bootstrap.  Supports user authentication (JWT), product CRUD, and responsive UI.

---

## Table of Contents


- [Project Overview](#project-overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Getting Started (Without Docker)](#getting-started-without-docker)
  - [1. Clone the Repository](#1-clone-the-repository)
  - [2. Install Dependencies (Manual Setup)](#2-install-dependencies-manual-setup)
  - [3. Environment Configuration (Manual Setup)](#3-environment-configuration-manual-setup)
  - [4. Database Setup (Manual Setup)](#4-database-setup-manual-setup)
  - [5. Run the Application (Manual Setup)](#5-run-the-application-manual-setup)
- [Getting Started (With Docker)](#getting-started-with-docker) <!-- NEW SECTION -->
  - [1. Clone the Repository (if not already done)](#1-clone-the-repository-if-not-already-done)
  - [2. Environment Configuration (for Docker)](#2-environment-configuration-for-docker)
  - [3. Build and Run Docker Containers](#3-build-and-run-docker-containers)
  - [4. Initialize Application Inside Docker](#4-initialize-application-inside-docker)
  - [5. Access the Application (Docker)](#5-access-the-application-docker)
  - [6. Frontend Development (with Docker)](#6-frontend-development-with-docker)
  - [7. Stopping Docker Containers](#7-stopping-docker-containers)
- [API Endpoints](#api-endpoints)
  - [Authentication](#authentication)
  - [Products](#products)
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
*   Dockerized environment for development.

## Tech Stack

*   **Backend:** Laravel (PHP Framework)
*   **Frontend:** HTML, Bootstrap 5, JavaScript (Vanilla JS with Blade templating)
*   **Database:** MySQL 8.0
*   **Web Server (in Docker):** Nginx
*   **PHP Version (in Docker):** 8.2 (or your specified version, e.g., 8.1)
*   **Authentication:** JWT (`tymon/jwt-auth` package)
*   **Containerization:** Docker, Docker Compose
*   **Node.js Version (for host development):** 18.x or 20.x (Specify your version)
*   **Composer**
*   **NPM**

## Prerequisites

**For Manual (Non-Docker) Setup:**
*   PHP (version matching Docker, e.g., 8.2)
*   Composer ([https://getcomposer.org/](https://getcomposer.org/))
*   Node.js and npm (versions specified above) ([https://nodejs.org/](https://nodejs.org/))
*   A local MySQL Server instance (e.g., via WAMP, XAMPP, MAMP, or standalone)
*   A code editor (e.g., VS Code)
*   An API client (e.g., Postman, Insomnia)

**For Docker Setup:**
*   Docker Desktop ([https://www.docker.com/products/docker-desktop/](https://www.docker.com/products/docker-desktop/))
*   Node.js and npm (for running `npm run dev` on the host, if preferred)
*   A code editor
*   An API client
*   Git (optional, for version control)

## Getting Started (Without Docker)

Follow these steps if you are **not** using Docker and want to set up the project directly on your local machine (e.g., using WAMP/XAMPP/MAMP).

### 1. Clone the Repository

```bash
# If you have it on GitHub/GitLab:
# git clone <your-repository-url>
# cd mini_product_inventory_app_full_stack

# If you are just setting it up from your local files, ensure you are in the project root:
# cd C:\path\to\your\project\mini_product_inventory_app_full_stack
```

### 2. Install Dependencies (Manual Setup)

Install backend (PHP) and frontend (Node.js) dependencies:
```bash
composer install
npm install
```

### 3. Environment Configuration (Manual Setup)

*   Copy the `.env.example` file to a new file named `.env`:
    ```bash
    cp .env.example .env
    ```
    (On Windows, you can use `copy .env.example .env`)
*   Open the `.env` file and configure your **local** database connection and other settings:
    ```dotenv
    APP_NAME="Mini Product Inventory"
    APP_ENV=local
    APP_KEY= # This will be generated in the next step
    APP_DEBUG=true
    APP_URL=http://localhost:8000 # Or your local development server URL

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306 # Or your local MySQL port (e.g., 3308 if you changed it)
    DB_DATABASE=inventory_app_db # Your chosen database name
    DB_USERNAME=root             # Your local MySQL username
    DB_PASSWORD=                 # Your local MySQL password

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

### 4. Database Setup (Manual Setup)

*   Ensure your local MySQL server is running.
*   Create a new database in MySQL (e.g., using phpMyAdmin) with the name you specified in `DB_DATABASE`.
*   Run the database migrations:
    ```bash
    php artisan migrate
    ```
    *Note: If you encounter a "Specified key was too long" error, ensure `Schema::defaultStringLength(191);` is in `app/Providers/AppServiceProvider.php`.*

### 5. Run the Application (Manual Setup)

*   Start the Laravel development server:
    ```bash
    php artisan serve
    ```
*   Start the Vite development server (in a separate terminal):
    ```bash
    npm run dev
    ```
*   Access the application: `http://localhost:8000/login` (or your `APP_URL`).

---

## Getting Started (With Docker)

Follow these steps to run the application using Docker and Docker Compose. This is the recommended way for a consistent development environment.

### 1. Clone the Repository (if not already done)
```bash
# If you have it on GitHub/GitLab:
# git clone <your-repository-url>
# cd mini_product_inventory_app_full_stack

# If you are just setting it up from your local files, ensure you are in the project root:
# cd C:\path\to\your\project\mini_product_inventory_app_full_stack
```

### 2. Environment Configuration (for Docker)

*   Copy the `.env.example` file to a new file named `.env`:
    ```bash
    cp .env.example .env
    ```
    (On Windows, you can use `copy .env.example .env`)
*   Open the `.env` file. **Ensure the following settings are configured for Docker:**
    ```dotenv
    APP_NAME="Mini Product Inventory (Docker)"
    APP_ENV=local
    APP_KEY= # Will be generated
    APP_DEBUG=true
    APP_URL=http://localhost:8000 # Or ${APP_DOCKER_PORT} if you set that in .env
    APP_DOCKER_PORT=8000 # Port Nginx will listen on the host

    DB_CONNECTION=mysql
    DB_HOST=db # Service name of the MySQL container
    DB_PORT=3306 # Internal port MySQL listens on inside Docker
    DB_DATABASE=inventory_app_db # Must match MYSQL_DATABASE in docker-compose.yml
    DB_USERNAME=devuser          # Must match MYSQL_USER in docker-compose.yml
    DB_PASSWORD=devuser          # Must match MYSQL_PASSWORD in docker-compose.yml
    DB_ROOT_PASSWORD=yourstrongrootpassword # Add a strong root password for the MySQL container itself
    DB_DOCKER_PORT=33061 # Port MySQL will be accessible on the host (optional)

    JWT_SECRET= # Will be generated

    # For Vite HMR when npm run dev is on host and app is in Docker:
    # VITE_DEV_SERVER_URL=http://localhost:5173 # (Adjust if your Vite port is different)
    ```
    **Important:** The `DB_HOST` must be `db` for the Laravel application inside Docker to connect to the MySQL container. `DB_USERNAME` and `DB_PASSWORD` must match the `MYSQL_USER` and `MYSQL_PASSWORD` environment variables set for the `db` service in `docker-compose.yml`.

### 3. Build and Run Docker Containers

*   Ensure Docker Desktop is running.
*   From the project root directory, run:
    ```bash
    docker-compose up -d --build
    ```
    The first build might take some time as it downloads base images and installs dependencies.

### 4. Initialize Application Inside Docker

Once the containers are up and running (`docker-compose ps` to check):

*   **Generate Application Key:**
    ```bash
    docker-compose exec app php artisan key:generate
    ```
*   **Generate JWT Secret:**
    ```bash
    docker-compose exec app php artisan jwt:secret
    ```
*   **Run Database Migrations:**
    (Wait a few moments for the database container to be fully ready)
    ```bash
    docker-compose exec app php artisan migrate
    ```
*   **(Optional) Install Composer dependencies (if not handled by Dockerfile or if `vendor` is in `.dockerignore`):**
    ```bash
    # docker-compose exec app composer install
    ```
*   **(Optional) Set file permissions if needed:**
    ```bash
    # docker-compose exec app chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache
    # docker-compose exec app chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache
    ```

### 5. Access the Application (Docker)

*   Open your web browser and navigate to `http://localhost:8000` (or the port you set for `APP_DOCKER_PORT`).
    *   Login: `http://localhost:8000/login`
    *   Register: `http://localhost:8000/register`

### 6. Frontend Development (with Docker)

*   For compiling frontend assets (CSS, JS) and enabling Hot Module Replacement (HMR):
    Open a **separate terminal on your host machine** (not inside a Docker container), navigate to the project root, and run:
    ```bash
    npm run dev
    ```
    Keep this running. Your browser (accessing `http://localhost:8000`) will connect to this Vite dev server for assets.
    *Ensure `VITE_DEV_SERVER_URL=http://localhost:5173` (or your Vite port) is set in your `.env` if you encounter issues with asset loading or HMR.*

### 7. Stopping Docker Containers

*   To stop the containers:
    ```bash
    docker-compose down
    ```
*   To stop and remove volumes (including database data - **use with caution!**):
    ```bash
    docker-compose down -v
    ```

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

see the docker section
Currently, to run locally the application is run using `php artisan serve` for the backend and `npm run dev` for frontend assets.

##### misc
![Visitor Count](https://visitor-badge.laobi.icu/badge?page_id=AzwadFawadHasan.mini_product_inventory_app_full_stack)

## some screenshots:
![image](https://github.com/user-attachments/assets/61892842-cf7e-4506-91a5-8c0f4ff60863)
![image](https://github.com/user-attachments/assets/d6ab3490-662b-4e6f-aaf0-8a9f793a0a5e)
![image](https://github.com/user-attachments/assets/1d46d3a5-0eb0-41e0-a4d3-29c0b23016dc)
![image](https://github.com/user-attachments/assets/a3bc0d81-c27c-45bd-8e4c-6cd9b354f97d)
![image](https://github.com/user-attachments/assets/7da25ae1-660b-4bdc-a752-55b4a403a3e5)
![image](https://github.com/user-attachments/assets/14cd7a45-1cfa-42e4-8634-1095f87e944b)
![image](https://github.com/user-attachments/assets/34680483-522b-42e1-99a1-e4ca577a1a31)







