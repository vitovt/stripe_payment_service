# Accept payments with Stripe Checkout using standalone from

This app use Stripe PHP framework from examples and simple html form.
Also it tracks all payments in mysql database.

## Requirements

* PHP

## How to run

1. Confirm `.env` configuration

Copy `.env.sample` to `.env` in this server directory, replace with your Stripe API keys:

```sh
cp .env.sample .env
```
2. Install dependencies with composer

From the directory that contains composer.json, run:

```
composer install
```

3. Run the server locally

Start the server from the public directory with:

```
cd public
php -S localhost:4242
```

3.1. Or use external web-server with php (apache2, nginx)
