# 🛒 E-Commerce RESTful API

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Postman](https://img.shields.io/badge/Postman-Documented-FF6C37?style=for-the-badge&logo=postman&logoColor=white)](https://postman.com)

A high-performance, enterprise-grade E-Commerce backend RESTful API built with **Laravel 11** and **PHP 8.2+**. Engineered around **Clean Architecture**, decoupled **Service Layers**, **ACID Transactions**, **Pessimistic Concurrency Controls**, cryptographic payment verification via **Paymob**, and asynchronous **Event-Driven Queues**.

---

## 📖 Interactive API Documentation

Explore full endpoint schemas, request headers, query parameters, and JSON response models via Fern or Postman:

- 🌐 **[Live Interactive Documentation (Fern Hosted)](https://e-commerce-api.docs.buildwithfern.com)**
- 📁 **[Postman Collection Export](https://documenter.getpostman.com/view/56053649/2sBYHNXi8T)** *(Optional workspace import)*

---

## 🛠️ Tech Stack & Key Technologies

* **Framework & Core:** PHP 8.2+ / Laravel 11.x
* **Database & ORM:** MySQL 8.0+ / Eloquent ORM
* **Authentication Engine:** Laravel Sanctum (Bearer Token Authorization)
* **Payment Processing:** Paymob Accept API (Cards, Digital Wallets, Callback Webhooks)
* **Asynchronous Jobs & Queues:** Laravel Queues (Database / Redis drivers)
* **Event Dispatcher:** Laravel Events & Queued Listeners
* **File Storage & Cleanup:** Laravel Storage (Local / Public Disk) with Automated Model Observers
* **Testing & Documentation:** Fern / Postman Documentation

---

## 🏗️ Architecture, Design Patterns & Clean Code

The application adheres strictly to **Clean Code**, **SOLID principles**, and strict **Separation of Concerns**:

* **Dedicated Service Layer:**
  * Complex multi-step business logic is completely isolated from HTTP Controllers into specialized domain services:
    * `PaymobService`: Handles payment integration pipelines, auth token generation, order registration, and payment keys.
    * `OrderService`: Manages checkout lifecycles, totals calculation, and status transitions.
    * `CartService`: Encapsulates cart state manipulations and inventory validation.
  * Controllers remain thin, focusing exclusively on HTTP request orchestration and standardized responses.

* **Encapsulated Data Transformation (API Resources):**
  * All responses are structured through dedicated **Laravel API Resources & Resource Collections** (`ProductResource`, `OrderResource`, `CartResource`, `UserResource`).
  * Prevents schema leakage and provides uniform JSON contracts decoupled from database column names.

* **Strict Validation via Custom Form Requests:**
  * Zero validation logic in controllers; all incoming payloads are sanitized and validated via dedicated **Form Request classes** (`StoreProductRequest`, `CheckoutRequest`, `RegisterRequest`, etc.), enforcing authorization rules and unified `422 Unprocessable Content` responses.

* **Type-Safe Domain States (PHP 8.2+ Backed Enums):**
  * Business rules and database flags are guarded using native Backed Enums:
    * `OrderStatus` (`Pending`, `Processing`, `Delivered`, `Canceled`)
    * `PaymentStatus` (`Unpaid`, `Paid`, `Failed`, `Refunded`)
    * `PaymentMethod` (`Card`, `Wallet`, `CashOnDelivery`)
    * `UserRole` (`Admin`, `Customer`)

* **Model-Level Authorization (Laravel Policies):**
  * Granular policy enforcement (`OrderPolicy`, `CartPolicy`, `ProductPolicy`) verifying entity ownership and role privileges prior to write or update operations.

* **Automated Media Cleanup (Model Observers):**
  * Clean filesystem maintenance using **Laravel Model Observers** that automatically purge unlinked physical assets from `storage/` whenever records or image relations are updated or destroyed, preventing orphaned files.

---

## ⚡ Database Optimization, Concurrency & Transactions

* **Eliminating the N+1 Query Problem:**
  * Strict implementation of **Eager Loading (`with(['category', 'subCategory', 'images'])`)** across relational endpoints, optimizing memory consumption and eliminating redundant queries.

* **Concurrency Control & Pessimistic Locking:**
  * High-concurrency checkout operations utilize **Pessimistic Locking (`lockForUpdate()`)** combined with atomic inventory adjustments to eliminate **Race Conditions**, stock over-selling, and negative inventory states.

* **ACID Transactions (`DB::transaction`):**
  * Critical checkout flows—including inventory deduction, cart clearing, order persistence, and payment token generation—are wrapped inside database transactions, guaranteeing atomic commits and immediate rollbacks on failure.

* **Modular Query Scopes:**
  * Domain-specific query filters encapsulated inside Eloquent **Query Scopes** (`scopeActive()`, `scopeFilter()`, `scopeSort()`), maintaining clean, readable, and reusable database queries.

---

## 🔒 Payment Engineering & Asynchronous Workflows

* **Resilient HTTP Communication:**
  * Direct integration with the **Paymob Accept API** using the **Laravel HTTP Client (`Http::timeout()->post(...)`)**, incorporating structured error handling and connection retries.

* **Webhook HMAC Hash Verification:**
  * Strict cryptographic verification of incoming Paymob Webhook callbacks via **HMAC SHA-512 signatures**, validating data integrity and shielding the system against payload tampering or spoofed payment success calls.

* **Asynchronous Queue Pipeline & Event Listeners:**
  * Non-blocking architecture where order placement and payment state transitions dispatch decoupled **Laravel Events & Queued Listeners** (`OrderPlacedEvent`, `PaymentSuccessEvent`), offloading invoice generation and transactional email notifications from the main execution thread.

---

## 📂 Core Endpoints Breakdown

| Module | Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- | :--- |
| **Auth** | `POST` | `/api/register` | Register customer account & generate token | No |
| **Auth** | `POST` | `/api/login` | Authenticate user credentials & issue token | No |
| **Auth** | `POST` | `/api/logout` | Revoke active access token | Yes (Bearer) |
| **Profile** | `GET` | `/api/showProfile` | Retrieve authenticated user profile | Yes (User) |
| **Profile** | `PATCH` | `/api/editProfile` | Update user details, phone, and address | Yes (User) |
| **Products** | `GET` | `/api/products` | Dynamic filtering, search, and pagination | No |
| **Products** | `POST` | `/api/products` | Create product with cover image & attributes | Yes (Admin) |
| **Products** | `POST` | `/api/products/{id}/image`| Upload additional gallery images | Yes (Admin) |
| **Categories** | `GET` | `/api/categories` | List hierarchical categories & sub-categories | No |
| **Categories** | `POST` | `/api/categories` | Create product category | Yes (Admin) |
| **Cart** | `POST` | `/api/carts` | Add / increment items in shopping cart | Yes (User) |
| **Cart** | `GET` | `/api/carts` | View cart items, quantity, and computed total | Yes (User) |
| **Cart** | `DELETE`| `/api/carts/{id}` | Remove specific cart item | Yes (User) |
| **Checkout** | `POST` | `/api/carts/checkout` | Trigger order lifecycle & Paymob payment URL | Yes (User) |
| **Orders** | `GET` | `/api/order/my-orders` | View authenticated customer order history | Yes (User) |
| **Orders** | `GET` | `/api/orders` | Search, filter, and review orders | Yes (Admin) |
| **Orders** | `PATCH`| `/api/orders/{id}` | Update order status (e.g., delivered) | Yes (Admin) |
| **Dashboard**| `GET` | `/api/dashboard` | Administrative overview metrics & statistics | Yes (Admin) |

---

## 🚀 Getting Started Locally

### 1. Clone & Install
```bash
git clone [https://github.com/your-username/your-repo-name.git](https://github.com/your-username/your-repo-name.git)
cd your-repo-name
composer install
