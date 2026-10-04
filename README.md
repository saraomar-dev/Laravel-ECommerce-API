# 🛒 E-Commerce RESTful API

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge\&logo=laravel\&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge\&logo=php\&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge\&logo=mysql\&logoColor=white)](https://mysql.com)
[![Postman](https://img.shields.io/badge/Postman-Documented-FF6C37?style=for-the-badge\&logo=postman\&logoColor=white)](https://postman.com)

A production-oriented E-Commerce RESTful API built with **Laravel 11** and **PHP 8.2+**, focusing on secure authentication, transactional checkout, concurrency control, payment integration, clean code, and maintainable backend architecture.

The project implements real-world backend concerns including **ACID transactions, pessimistic locking, role-based authorization, API Resources, Form Requests, Paymob payment integration, webhook verification, Events, and Queued Listeners**.

---

## 📖 Interactive API Documentation

Explore the available endpoints, request parameters, authentication requirements, and JSON response structures:

* 🌐 **[Live Interactive Documentation — Fern](https://e-commerce-api.docs.buildwithfern.com)**
* 📁 **[Postman Collection / Documentation](https://documenter.getpostman.com/view/56053649/2sBYHNXi8T)**

---

## 🛠️ Tech Stack

* **Backend:** PHP 8.2+ / Laravel 11
* **Database:** MySQL 8.0+ / Eloquent ORM
* **Authentication:** Laravel Sanctum / Bearer Tokens
* **Payments:** Paymob Accept API
* **Queues:** Laravel Queues
* **Events:** Laravel Events & Queued Listeners
* **Storage:** Laravel Filesystem
* **API Documentation:** Fern / Postman

---

## ✨ Key Features

### 🔐 Authentication & Authorization

* User registration and login with Laravel Sanctum.
* Bearer token authentication.
* Token revocation on logout.
* Role-based access control for Admin and Customer users.
* Laravel Policies for resource-level authorization.
* Ownership checks for user-specific resources.

### 🛍️ Product & Category Management

* Hierarchical categories and sub-categories.
* Product creation and management.
* Product cover images and additional gallery images.
* Product activation/deactivation.
* Inventory and stock management.
* Search, filtering, sorting, and pagination.
* Eloquent Query Scopes for reusable filtering logic.

### 🛒 Shopping Cart & Checkout

* User-specific shopping carts.
* Add, increment, and remove cart items.
* Stock availability validation.
* Automatic cart totals.
* Checkout workflow with shipping address and shipping fees.
* Order creation with price snapshots.
* Inventory deduction during checkout.

### 📦 Order Management

* Customer order history.
* Admin order management.
* Order status transitions.
* Payment status tracking.
* Search and filtering for administrative order management.
* Dedicated API Resources for consistent order responses.

---

## 🏗️ Architecture & Clean Code

The application follows **Separation of Concerns**, **SOLID principles**, and a layered service-oriented approach.

### Dedicated Service Layer

Complex business logic is separated from HTTP controllers into dedicated services:

* `PaymobService` — Handles payment integration, authentication tokens, order registration, and payment keys.
* `OrderService` — Handles checkout workflows, order creation, totals, and order status logic.
* `CartService` — Handles cart operations, stock validation, and cart state management.

Controllers remain focused on request orchestration and returning standardized API responses.

### API Resources

Responses are transformed using dedicated Laravel API Resources and Resource Collections:

* `ProductResource`
* `OrderResource`
* `CartResource`
* `UserResource`

This provides consistent JSON responses while keeping API contracts separated from the underlying database structure.

### Form Requests

Request validation and authorization are handled through dedicated Form Request classes such as:

* `StoreProductRequest`
* `CheckoutRequest`
* `RegisterRequest`

This keeps validation rules outside controllers and provides consistent validation responses.

### Backed Enums

Native PHP Backed Enums are used for important domain states:

* `OrderStatus`
* `PaymentStatus`
* `PaymentMethod`
* `UserRole`

This provides type-safe and predictable handling of business states.

### Laravel Policies

Resource-level authorization is implemented using Laravel Policies, including:

* `OrderPolicy`
* `CartPolicy`
* `ProductPolicy`

Policies verify ownership and user privileges before protected operations are performed.

### Model Observers

Laravel Model Observers are used for automated media cleanup, removing obsolete files from storage when related image records are updated or deleted.

---

## ⚡ Database Optimization, Concurrency & Transactions

### Preventing N+1 Queries

Relationship eager loading is used across relational endpoints to prevent N+1 query problems and reduce unnecessary database queries.

Example:

```php
with(['category', 'subCategory', 'images'])
```

### Pessimistic Locking

Checkout operations use:

```php
lockForUpdate()
```

to lock inventory records during critical operations.

This helps prevent race conditions and overselling when multiple checkout requests attempt to modify the same stock simultaneously.

### ACID Transactions

Critical checkout operations are wrapped in:

```php
DB::transaction()
```

ensuring that related database changes are committed together or rolled back when an exception occurs.

The checkout workflow includes operations such as:

* Inventory updates
* Order creation
* Order item creation
* Cart completion
* Payment-related persistence

### Query Scopes

Reusable Eloquent Query Scopes are used for domain-specific filtering and sorting, including:

* `scopeActive()`
* `scopeFilter()`
* `scopeSort()`

---

## 💳 Payment Integration & Asynchronous Workflows

### Paymob Integration

The API integrates with the **Paymob Accept API** for online payment processing.

The payment workflow includes:

* Paymob authentication.
* Gateway order registration.
* Payment key generation.
* Card and wallet payment support.
* Payment callbacks/webhooks.
* Payment status updates.

### Webhook HMAC Verification

Incoming Paymob webhook callbacks are verified using **HMAC SHA-512** before processing payment state changes.

This helps ensure that payment notifications are authentic and prevents unauthorized requests from being treated as successful payments.

### Events & Queued Listeners

The application uses Laravel Events and Queued Listeners to decouple asynchronous operations from the main request lifecycle.

Examples include:

* `OrderPlacedEvent`
* `PaymentSuccessEvent`

Background processing can be used for operations such as invoice generation and transactional notifications without blocking the main API request.

---

## 📂 Core API Endpoints

| Module         | Method   | Endpoint                   | Description                                      | Authentication |
| :------------- | :------- | :------------------------- | :----------------------------------------------- | :------------- |
| **Auth**       | `POST`   | `/api/register`            | Register a customer and generate an access token | Public         |
| **Auth**       | `POST`   | `/api/login`               | Authenticate user credentials and issue a token  | Public         |
| **Auth**       | `POST`   | `/api/logout`              | Revoke the current access token                  | User           |
| **Profile**    | `GET`    | `/api/showProfile`         | Retrieve authenticated user profile              | User           |
| **Profile**    | `PATCH`  | `/api/editProfile`         | Update user profile information                  | User           |
| **Products**   | `GET`    | `/api/products`            | Search, filter, sort, and paginate products      | Public         |
| **Products**   | `POST`   | `/api/products`            | Create a product with attributes and cover image | Admin          |
| **Products**   | `POST`   | `/api/products/{id}/image` | Upload additional product gallery images         | Admin          |
| **Categories** | `GET`    | `/api/categories`          | Retrieve categories and sub-categories           | Public         |
| **Categories** | `POST`   | `/api/categories`          | Create a product category                        | Admin          |
| **Cart**       | `POST`   | `/api/carts`               | Add or increment cart items                      | User           |
| **Cart**       | `GET`    | `/api/carts`               | Retrieve cart items and calculated totals        | User           |
| **Cart**       | `DELETE` | `/api/carts/{id}`          | Remove a cart item                               | User           |
| **Checkout**   | `POST`   | `/api/carts/checkout`      | Process checkout and initiate payment            | User           |
| **Orders**     | `GET`    | `/api/order/my-orders`     | Retrieve authenticated user's orders             | User           |
| **Orders**     | `GET`    | `/api/orders`              | Search and filter orders                         | Admin          |
| **Orders**     | `PATCH`  | `/api/orders/{id}`         | Update order status                              | Admin          |
| **Dashboard**  | `GET`    | `/api/dashboard`           | Retrieve administrative statistics               | Admin          |

---

## 🚀 Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/your-repository.git
cd your-repository
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database and required environment variables in `.env`.

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Create Storage Link

```bash
php artisan storage:link
```

### 6. Start the Application

```bash
php artisan serve
```

The API will be available at:

```text
http://127.0.0.1:8000
```

---

## 🔧 Queue Configuration

For asynchronous jobs, configure the desired queue driver in `.env`.

Example:

```env
QUEUE_CONNECTION=database
```

Then run the queue worker:

```bash
php artisan queue:work
```

---

## 🧪 Testing

API endpoints can be tested using the provided **Postman Collection** and the interactive **Fern documentation**.

For production-level deployments, Laravel's feature and HTTP testing capabilities can also be used to automate endpoint and business-flow verification.

---

## 📌 Project Highlights

This project demonstrates practical backend engineering concepts beyond basic CRUD operations:

* RESTful API design
* Authentication with Laravel Sanctum
* Role-based authorization
* Laravel Policies
* Service Layer architecture
* Form Requests
* API Resources
* PHP Backed Enums
* Database Transactions
* Pessimistic Locking
* Race Condition Prevention
* Query Scopes
* Eager Loading / N+1 Prevention
* Paymob Payment Integration
* Webhook HMAC Verification
* Laravel Events
* Queued Listeners
* File Storage Management
* API Documentation

---

## 📈 Future Improvements

Potential extensions include:

* Automated Feature & Integration Tests
* Redis-based production queue infrastructure
* Dockerized development environment
* CI/CD pipeline
* Frontend client application
* Product reviews and ratings
* Wishlist functionality
* Advanced caching and performance monitoring

---

## 👩‍💻 Author

**Sara Omar**

Backend-focused developer building practical RESTful APIs with Laravel and PHP.

[GitHub](https://github.com/your-username)
