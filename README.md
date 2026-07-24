# Mini Order Management API

## Tech Stack
- Laravel 10+
- PHP 8+
- MySQL
- Laravel Sanctum
- Redis
- Swagger


## Setup

git clone

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate

php artisan serve


## Authentication APIs

POST /api/register

POST /api/login

POST /api/logout


## Product APIs

GET    /api/products

POST   /api/products

GET    /api/products/{id}

PUT    /api/products/{id}

DELETE /api/products/{id}


## Order APIs

POST /api/orders

GET /api/orders

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
6. Update inventory
