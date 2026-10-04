# 🛒 E-Commerce RESTful API

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge\&logo=laravel\&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge\&logo=php\&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge\&logo=mysql\&logoColor=white)](https://mysql.com)
[![Postman](https://img.shields.io/badge/Postman-Documented-FF6C37?style=for-the-badge\&logo=postman\&logoColor=white)](https://postman.com)

A production-oriented **E-Commerce RESTful API** built with **Laravel 11**, **PHP 8.2+**, and **MySQL**.

The application implements a complete e-commerce backend workflow including authentication, role-based authorization, product and category management, shopping carts, inventory management, transactional checkout, order management, Cash on Delivery, online payments through Paymob, webhook verification, asynchronous events, queued notifications, and administrative order control.

The project focuses on practical backend engineering concepts such as **Service Layers, API Resources, Form Requests, Middleware, Policies, Database Transactions, Pessimistic Locking, Events, Queues, Webhooks, and Payment Integration**.

---

## 📖 Interactive API Documentation

Explore and manually test the API through the provided documentation:

* 🌐 **[Live Interactive Documentation — Fern](https://e-commerce-api.docs.buildwithfern.com)**
* 📁 **[Postman Collection / Documentation](https://documenter.getpostman.com/view/56053649/2sBYHNXi8T)**

The documentation provides available endpoints, authentication requirements, request parameters, request bodies, and API responses.

---

# ✨ Core Features

## 🔐 Authentication & Authorization

* Customer registration and login.
* Authentication using **Laravel Sanctum**.
* Bearer token authorization.
* Token revocation on logout.
* Role-based access control for **Admin** and **Customer** users.
* Authentication middleware for protected API routes.
* Role-based middleware for administrative endpoints.
* Laravel Policies for resource-level authorization.
* Ownership validation to prevent users from accessing or modifying other users' resources.

---

## 👤 Customer Profile

Authenticated customers can:

* View their profile.
* Update personal information.
* Update phone number.
* Update address information.
* Access their own cart.
* Access their own order history.

Profile operations are protected through authentication middleware and authorization rules.

---

## 🗂️ Category & Sub-Category Management

The catalog supports hierarchical product organization:

```text
Category
   ├── Sub-category
   ├── Sub-category
   └── Sub-category
```

Admins can:

* Create categories.
* Create sub-categories.
* Manage product categorization.

Customers can browse publicly available categories and their related products.

---

## 📦 Product Management

Products support:

* Name.
* Description.
* Brand.
* SKU.
* Price.
* Stock quantity.
* Category.
* Sub-category.
* Cover image.
* Additional gallery images.
* Active/inactive status.

### Product Visibility

Only active products are exposed through the public customer-facing product listing.

Administrative operations can manage product status and inventory.

---

## 🖼️ Product Image Management

The API supports:

* Product cover image upload.
* Additional product gallery images.
* Multiple images per product.
* Image storage through Laravel Storage.
* Automatic cleanup of obsolete files using Model Observers.

The implementation prevents unused physical files from accumulating in storage when related image records are updated or deleted.

---

## 🔎 Product Search, Filtering & Pagination

The product listing API supports:

* Search by product name.
* Search by brand.
* Search by description.
* SKU-based search for administrative operations.
* Filtering.
* Sorting.
* Pagination.
* Active-product filtering.
* Stock-aware customer product listings.

Reusable Eloquent Query Scopes keep filtering and sorting logic modular and maintainable.

---

# 🔄 Complete E-Commerce Workflow

The application connects the main e-commerce operations into a complete backend workflow:

```text
                    ADMIN
                      │
          ┌───────────┴───────────┐
          ↓                       ↓
     Categories                Products
                                  │
                                  ↓
                           Stock & Images
                                  │
                                  ▼
                            CUSTOMER
                                  │
                                  ↓
                         Browse Products
                                  │
                                  ↓
                              Add to Cart
                                  │
                                  ↓
                            View Cart
                                  │
                                  ↓
                              Checkout
                                  │
                    ┌─────────────┴─────────────┐
                    ↓                           ↓
             Validate Stock              Calculate Total
                    │                           │
                    └─────────────┬─────────────┘
                                  ↓
                         Database Transaction
                                  │
                         Pessimistic Lock
                                  │
                    ┌─────────────┴─────────────┐
                    ↓                           ↓
                   COD                     Paymob
                    │                           │
                    │                    Payment Gateway
                    │                           │
                    │                    Callback / Webhook
                    │                           │
                    │                     HMAC Verification
                    │                           │
                    └─────────────┬─────────────┘
                                  ↓
                           Order Created
                                  │
                           Stock Deducted
                                  │
                           Cart Completed
                                  │
                                  ↓
                           Order Status
                                  │
                                  ↓
                              Event
                                  │
                                  ↓
                               Queue
                                  │
                                  ↓
                            Notification
                                  │
                                  ↓
                              CUSTOMER
```

---

# 🛒 Shopping Cart

Each authenticated customer has an active cart.

Customers can:

* Add products to the cart.
* Increment product quantities.
* View cart contents.
* View calculated cart totals.
* Remove individual cart items.

Cart operations include:

* Product existence validation.
* Product availability validation.
* Stock validation.
* User ownership checks.
* Quantity management.

The cart does **not** permanently deduct inventory when an item is added.

Stock is validated and deducted during the checkout process.

---

# 💰 Price Snapshotting

When an order is created, each order item stores the product price at the time of purchase.

```text
Product price at purchase
          ↓
      Order Item
          ↓
    Price Snapshot
```

This ensures that changing the product's current price does not modify the financial information of existing orders.

---

# 📦 Inventory Management

Inventory is handled as part of the checkout workflow.

The system:

* Validates available stock.
* Prevents purchasing unavailable quantities.
* Deducts stock during checkout.
* Prevents negative inventory.
* Protects inventory from concurrent checkout requests.

Stock is **not deducted simply by adding a product to the cart**.

---

# 🚚 Shipping

Checkout supports shipping information including:

* Shipping address.
* Shipping fee.

The shipping fee is included when calculating the final order total.

The shipping address is stored with the order so historical orders retain the address used during checkout.

---

# 🧾 Checkout Workflow

Checkout is implemented as a complete transactional business workflow rather than a simple order creation endpoint.

```text
Customer Cart
      ↓
Validate Cart
      ↓
Validate Products
      ↓
Validate Stock
      ↓
Calculate Product Totals
      ↓
Add Shipping Fee
      ↓
Create Order
      ↓
Create Order Items
      ↓
Snapshot Product Prices
      ↓
Process Payment Method
      ↓
Deduct Inventory
      ↓
Complete Cart
      ↓
Dispatch Related Events
      ↓
Return Order / Payment Information
```

Critical operations are protected using database transactions and pessimistic locking.

---

# 🔒 Concurrency Control

Checkout uses **pessimistic locking** through:

```php
lockForUpdate()
```

This protects inventory during concurrent checkout requests.

Example:

```text
Stock = 5

Request A → 4 items
Request B → 3 items

Request A locks the inventory row
        ↓
Stock is re-validated
        ↓
Inventory is updated
        ↓
Request B continues
        ↓
Request B sees the updated stock
```

This prevents race conditions and stock overselling.

---

# 🗄️ ACID Transactions

Critical checkout operations are wrapped in:

```php
DB::transaction()
```

The transaction protects related operations including:

* Order creation.
* Order item creation.
* Inventory deduction.
* Cart state changes.
* Payment-related persistence.

If a critical operation fails, the transaction can roll back the related database changes instead of leaving the system in a partially completed state.

---

# 💳 Payment System

The application supports multiple payment methods.

## 💵 Cash on Delivery

Customers can choose **Cash on Delivery (COD)** during checkout.

The order is created with the appropriate payment method and payment state and can then be managed through the order lifecycle.

## 💳 Card / Online Payment

Online payment is integrated using the **Paymob Accept API**.

The integration includes:

* Paymob authentication.
* Gateway order registration.
* Payment key generation.
* Payment amount handling.
* Payment gateway identifiers.
* Payment callbacks.
* Payment transaction tracking.
* Payment status updates.
* Payment timestamps.

---

# 🔐 Paymob Webhook & HMAC Verification

The API verifies incoming Paymob callback/webhook requests using **HMAC SHA-512**.

```text
Paymob
   ↓
Callback / Webhook
   ↓
HMAC Verification
   ↓
 ┌─────────────┐
 │ Valid Hash? │
 └──────┬──────┘
        │
   ┌────┴────┐
   ↓         ↓
 Valid     Invalid
   ↓         ↓
Process    Reject
Payment    Request
```

Payment state changes are only processed after the callback integrity has been verified.

---

# 📋 Order Management

Orders contain information such as:

* Customer.
* Order items.
* Product references.
* Quantity.
* Price snapshots.
* Shipping address.
* Shipping fee.
* Order total.
* Payment method.
* Payment status.
* Order status.
* Payment gateway identifiers.
* Transaction identifiers.
* Payment timestamps.

---

# 👤 Customer Order Workflow

Customers can:

* View their own order history.
* Retrieve their order information.
* Track order status.
* Receive notifications related to important order changes.

Customers cannot access or modify unrelated users' orders.

This is enforced through authentication middleware and Laravel Policies.

---

# 👨‍💼 Admin Order Workflow

Administrators can:

* View customer orders.
* Search orders.
* Filter orders.
* Review order details.
* Update order status.
* Manage the order lifecycle.

The order status is represented using a native PHP Backed Enum.

Example lifecycle:

```text
Pending
   ↓
Processing
   ↓
Delivered
```

The system also supports the defined order states required by the application.

---

# 📊 Admin Dashboard

The API includes an administrative dashboard endpoint that provides high-level e-commerce statistics.

The dashboard is protected and available only to authorized administrators.

It provides an overview of important business data such as order and sales-related metrics implemented by the application.

---

# 🔔 Order Notifications

Important order lifecycle changes can trigger notifications for customers.

Example workflow:

```text
Admin Updates Order Status
          ↓
Order Status Event
          ↓
Queued Listener
          ↓
Notification
          ↓
Customer
```

This keeps notification processing separate from the main order-management request.

---

# ⚡ Events, Queues & Asynchronous Processing

The application uses Laravel Events and Queued Listeners to decouple background operations from the main HTTP request.

Implemented workflows include events related to:

* Order placement.
* Successful payment.
* Order status changes.
* Customer notifications.

Example:

```text
API Request
    ↓
Business Logic
    ↓
Event Dispatched
    ↓
Queue
    ↓
Queued Listener
    ↓
Notification / Background Task
```

This allows notification and other background operations to be processed asynchronously.

---

# 🛡️ Middleware & Policies

The API uses both **Middleware** and **Laravel Policies** for layered authorization.

## Middleware

Middleware is used to protect API routes based on:

* Authentication status.
* User role.
* Administrative privileges.

Example:

```text
Public
   ↓
Authenticated User
   ↓
Admin-only Routes
```

## Policies

Policies provide resource-level authorization.

Implemented policies include:

* `OrderPolicy`
* `CartPolicy`
* `ProductPolicy`

They enforce rules such as:

* A customer can only access their own cart.
* A customer can only access their own orders.
* Administrative actions require the appropriate role.
* Protected resources cannot be modified by unauthorized users.

---

# 🏗️ Architecture & Separation of Concerns

The application separates HTTP handling, validation, authorization, business logic, data transformation, and asynchronous processing into dedicated Laravel components.

```text
HTTP Request
     ↓
Middleware
     ↓
Form Request
     ↓
Controller
     ↓
Policy / Authorization
     ↓
Service Layer
     ↓
Eloquent Models
     ↓
Database
```

Asynchronous workflows follow a separate path:

```text
Business Operation
     ↓
Event
     ↓
Queue
     ↓
Queued Listener
     ↓
Notification / Background Task
```

## Service Layer

### `CartService`

Handles:

* Cart operations.
* Cart state.
* Quantity management.
* Stock validation.

### `OrderService`

Handles:

* Checkout workflow.
* Order creation.
* Order totals.
* Order items.
* Order status logic.
* Inventory-related operations.

### `PaymobService`

Handles:

* Paymob authentication.
* Gateway order registration.
* Payment key generation.
* Payment-related API communication.
* Payment processing workflow.

Controllers remain focused on HTTP request orchestration instead of containing the complete business logic.

---

# 📦 API Resources

Responses are transformed through dedicated Laravel API Resources and Resource Collections.

Examples include:

* `ProductResource`
* `OrderResource`
* `CartResource`
* `UserResource`

This provides consistent JSON response structures and separates the public API representation from the underlying database models.

---

# ✅ Form Requests & Validation

Dedicated Form Request classes handle request validation and authorization.

Examples include:

* `RegisterRequest`
* `StoreProductRequest`
* `CheckoutRequest`

Validation logic is kept outside controllers to maintain separation of concerns and consistent API behavior.

Invalid payloads return Laravel validation responses such as:

```text
422 Unprocessable Content
```

---

# 🧩 Domain Enums

Native PHP Backed Enums are used for important application states.

## Order Status

```text
Pending
Processing
Delivered
Canceled
```

## Payment Status

```text
Unpaid
Paid
Failed
Refunded
```

## Payment Method

```text
Card
Wallet
CashOnDelivery
```

## User Role

```text
Admin
Customer
```

Using enums prevents arbitrary status values from being introduced into business logic.

---

# 🧹 Automated Media Cleanup

Laravel Model Observers are used to keep filesystem storage synchronized with database records.

When product images or related image records are updated or removed, obsolete physical files can be automatically cleaned up.

This prevents orphaned media files from accumulating in storage.

---

# ⚡ Database Optimization

## Eager Loading

The API uses Eloquent eager loading to prevent N+1 query problems across relational endpoints.

Example:

```php
with(['category', 'subCategory', 'images'])
```

This reduces unnecessary database queries when retrieving related product information.

## Query Scopes

Reusable Eloquent Query Scopes are used for domain-specific filtering and sorting.

Examples include:

* `scopeActive()`
* `scopeFilter()`
* `scopeSort()`

---

# 📂 Core API Endpoints

| Module         | Method   | Endpoint                   | Description                 | Auth   |
| :------------- | :------- | :------------------------- | :-------------------------- | :----- |
| **Auth**       | `POST`   | `/api/register`            | Register customer           | Public |
| **Auth**       | `POST`   | `/api/login`               | Login and issue token       | Public |
| **Auth**       | `POST`   | `/api/logout`              | Revoke current token        | User   |
| **Profile**    | `GET`    | `/api/showProfile`         | View profile                | User   |
| **Profile**    | `PATCH`  | `/api/editProfile`         | Update profile              | User   |
| **Categories** | `GET`    | `/api/categories`          | List categories             | Public |
| **Categories** | `POST`   | `/api/categories`          | Create category             | Admin  |
| **Products**   | `GET`    | `/api/products`            | List/search/filter products | Public |
| **Products**   | `POST`   | `/api/products`            | Create product              | Admin  |
| **Products**   | `POST`   | `/api/products/{id}/image` | Add product image           | Admin  |
| **Cart**       | `POST`   | `/api/carts`               | Add/increment cart item     | User   |
| **Cart**       | `GET`    | `/api/carts`               | View current cart           | User   |
| **Cart**       | `DELETE` | `/api/carts/{id}`          | Remove cart item            | User   |
| **Checkout**   | `POST`   | `/api/carts/checkout`      | Process checkout            | User   |
| **Orders**     | `GET`    | `/api/order/my-orders`     | Customer order history      | User   |
| **Orders**     | `GET`    | `/api/orders`              | Manage orders               | Admin  |
| **Orders**     | `PATCH`  | `/api/orders/{id}`         | Update order status         | Admin  |
| **Dashboard**  | `GET`    | `/api/dashboard`           | Retrieve admin statistics   | Admin  |

For the complete endpoint list and request/response schemas, see the interactive API documentation.

---

# 🧪 API Testing

The API can be manually tested using:

* **Postman Collection**
* **Fern Interactive Documentation**

The documentation can be used to test:

* Authentication.
* Authorization.
* Products.
* Categories.
* Cart operations.
* Checkout.
* Orders.
* Payment workflows.
* Administrative operations.
* Notifications.

---

# 🚀 Getting Started

## 1. Clone the Repository

```bash
git clone https://github.com/saraomar-dev/Laravel-ECommerce-API.git
cd Laravel-ECommerce-API
```

## 2. Install Dependencies

```bash
composer install
```

## 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Configure the database and required application credentials inside `.env`.

For Paymob integration, configure the required gateway credentials according to the project environment.

## 4. Run Migrations

```bash
php artisan migrate
```

## 5. Create Storage Link

```bash
php artisan storage:link
```

## 6. Start the Application

```bash
php artisan serve
```

The API will be available at:

```text
http://127.0.0.1:8000
```

## 7. Start Queue Worker

For queued events, notifications, and background tasks:

```bash
php artisan queue:work
```

---

# 🧰 Technologies

* **PHP 8.2+**
* **Laravel 11**
* **MySQL 8.0+**
* **Laravel Sanctum**
* **Eloquent ORM**
* **Laravel Middleware**
* **Laravel Policies**
* **Laravel API Resources**
* **Laravel Form Requests**
* **Laravel Events**
* **Laravel Queues**
* **Laravel Notifications**
* **Laravel Storage**
* **Laravel Model Observers**
* **Paymob Accept API**
* **Postman**
* **Fern**

---

# 📌 What This Project Demonstrates

This project demonstrates practical backend engineering through a complete e-commerce workflow rather than basic CRUD operations.

### Backend & API

* RESTful API design.
* Laravel 11.
* PHP 8.2+.
* Laravel Sanctum authentication.
* Bearer token authorization.
* Middleware-based route protection.
* Role-based access control.
* Laravel Policies.
* Form Requests.
* API Resources.
* PHP Backed Enums.

### E-Commerce Business Logic

* Product and category management.
* Hierarchical categories and sub-categories.
* Product image galleries.
* Product activation/deactivation.
* Inventory management.
* Shopping cart management.
* Price snapshotting.
* Shipping address handling.
* Shipping fee calculation.
* Transactional checkout.
* Order lifecycle management.
* Customer order history.
* Administrative order management.
* Dashboard statistics.

### Database & Reliability

* MySQL / Eloquent ORM.
* Database transactions.
* ACID transaction handling.
* Pessimistic locking.
* Race-condition prevention.
* Stock overselling prevention.
* Eager Loading.
* N+1 query prevention.
* Query Scopes.

### Payments

* Paymob Accept API.
* Card payments.
* Digital wallet payments.
* Cash on Delivery.
* Payment callbacks/webhooks.
* HMAC SHA-512 verification.
* Payment status tracking.
* Gateway order and transaction identifiers.
* Payment timestamps.

### Asynchronous Processing

* Laravel Events.
* Queued Listeners.
* Laravel Queues.
* Order-related events.
* Payment-related events.
* Customer notifications.

### File Management

* Laravel Storage.
* Product image uploads.
* Multiple product images.
* Automated media cleanup.
* Model Observers.

### API Testing & Documentation

* Postman Collection.
* Interactive API documentation.
* Manual endpoint testing.
* Request/response documentation.

---

# 🚧 Current Scope & Future Improvements

The current implementation focuses on the core catalog, shopping cart, checkout, payment, inventory, and order-management workflows.

Potential future extensions include:

* Automated Feature / Integration Tests.
* Dockerized development environment.
* CI/CD pipeline.
* Redis-based production queue infrastructure.
* Wishlist functionality.
* Product reviews and ratings.
* Advanced caching.
* Frontend client application.
* Advanced analytics and dashboard features.

---

## 👩‍💻 Author

**Sara Omar**

Backend-focused developer building practical RESTful APIs with **Laravel and PHP**.
