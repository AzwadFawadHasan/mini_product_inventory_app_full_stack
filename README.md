# mini_product_inventory_app_full_stack
Building a small full-stack web app to manage products in inventory, built with Laravel (API + Blade), MySQL, and Bootstrap.  Supports user authentication (JWT), product CRUD, and responsive UI.

---

## Features

- **Authentication**
  - Register/Login with email and password
  - JWT-based authentication (Laravel backend)
- **Product Module**
  - CRUD for products: name, price, quantity, status (in stock / out of stock)
  - MySQL database
- **Frontend**
  - Login/Logout flow
  - Product list with Add/Edit/Delete
  - Form validation and error handling
  - Responsive design (Bootstrap)
- **Backend API**
  - RESTful API (Laravel)
  - Authentication middleware
  - Proper status codes and validation
- **Bonus**
  - Environment variable support via `.env`
  - Clean code structure and modularity

---

## Getting Started

### Prerequisites

- PHP 8.1+
- Composer
- MySQL
- Node.js & npm (optional, for asset compilation)
- [Optional] Docker & Docker Compose

### Installation

1. **Clone the repository**
    ```sh
    git clone https://github.com/AzwadFawadHasan/mini_product_inventory_app_full_stack.git
    cd mini_product_inventory_app_full_stack
    ```

2. **Install dependencies**
    ```sh
    composer install
    ```

3. **Copy and configure environment variables**
    ```sh
    cp .env.example .env
    # Edit .env to match your MySQL settings
    ```

4. **Generate application key**
    ```sh
    php artisan key:generate
    ```

5. **Run migrations**
    ```sh
    php artisan migrate
    ```

6. **(Optional) Seed the database**
    ```sh
    php artisan db:seed
    ```

7. **Start the development server**
    ```sh
    php artisan serve
    # Visit http://localhost:8080
    ```

---

## Docker (Optional)

To run with Docker and Docker Compose:

```sh
docker-compose up --build
```

- App: [http://localhost:8000](http://localhost:8080)
- MySQL: [localhost:3306](localhost:3308)

---

## Usage

- Register a new user or login.
- Add, edit, or delete products.
- Products require: name, price, quantity, and status.
- Logout when done.

---

## API Endpoints

- `POST /api/register` — Register new user
- `POST /api/login` — Login and receive JWT
- `POST /api/logout` — Logout (invalidate JWT)
- `GET /api/products` — List products (auth required)
- `POST /api/products` — Create product (auth required)
- `GET /api/products/{id}` — Get product details (auth required)
- `PUT /api/products/{id}` — Update product (auth required)
- `DELETE /api/products/{id}` — Delete product (auth required)

---

## Environment Variables

See `.env.example` for all configuration options.

---

## Contributing

Pull requests are welcome! For major changes, please open an issue first.

---

## License

[MIT](LICENSE)



