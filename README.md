# Mini Order Management API

A RESTful backend API built with Laravel.


## Tech Stack
- Laravel 13.8
- PHP 8.3
- PostGraySql
- Laravel Sanctum
- Redis
- Swagger
- Swagger URL ([/api/documentation](http://127.0.0.1:8000/api/documentation))


## Setup

git clone

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate

php artisan db:seed

php artisan serve


## Authentication APIs

- Register new user
POST /api/register

- Login User
POST /api/login

- Logout and revoke the token
POST /api/logout


## Product APIs

- Get all the products
- Product search filter by product_name & description, price 
GET    /api/products


- Create a product
POST   /api/products

- get single produt using id
GET    /api/products/{id}

- update the product detail using id
PUT    /api/products/{id}

- delete the product using id
DELETE /api/products/{id}


## Order APIs

- login user can place the order with multiple products
POST /api/orders

- get all login user order
GET /api/orders

- get order detail by id
GET /api/orders/{id}


## Features Implemented

### Redis Cache

Product listing API uses Redis cache.

Cache is invalidated after:
- Product creation
- Product update
- Product deletion


### Rate Limiting

Laravel throttle middleware is used.

Example:
- 60 requests/minute per user


### Database Transaction

Order creation uses DB transaction.

Flow:

1. Validate product stock
2. Lock product rows
3. Calculate total
4. Create order
5. Create order items
6. Update product stock
