# Exergame (Backend)

This README covers basic setup steps for the Laravel project as backend: installation, generating Passport keys, notes about `APP_KEY` usage in the frontend, and running migrations & seeders.

## Prerequisites

- PHP ^8.4
- Composer
- MySQL
- The PHP Sodium extension (`ext-sodium`) must be enabled for encryption/decryption features.
	- On most PHP installations, Sodium is bundled and enabled by default.
	- To check, run `php -m | findstr sodium` (Windows) or `php -m | grep sodium` (Linux/macOS).
	- If not present, install or enable it via your package manager or update your PHP configuration.



## 1) Install PHP dependencies

```powershell
composer install --no-interaction --prefer-dist
```

This installs PHP dependencies defined in `composer.json`.

## 2) Environment file

Copy the example env and adjust values (DB, MAIL, etc.):

```powershell
cp .env.example .env
```

On Windows PowerShell if `cp` is not available use `Copy-Item .env.example .env`.

After copying, set your database connection and other environment values in `.env`.

## 3) Generate Laravel application key

Laravel requires an application key (`APP_KEY`). Generate it with:

```powershell
php artisan key:generate
```

Note: The `APP_KEY` is used by Laravel to encrypt/decrypt data. This project requires the same `APP_KEY` to be available in the frontend when the frontend needs to encrypt or decrypt data in a compatible way; ensure you securely share this key with the frontend team and never commit it to source control.

## 4) Generate keys for Laravel Passport

This project uses Laravel Passport for API authentication. Generate Passport keys with:

```powershell
php artisan passport:install
```

## 5) Run database migrations and seeders

To run migrations and seed the database use Artisan:

```powershell
php artisan migrate --seed
```

If you need to force the migration in non-interactive environments (or to refresh):

```powershell
php artisan migrate --force --seed
# or
php artisan migrate:fresh --seed
```

There are convenient `composer` scripts in `composer.json` for initial project setup (see `post-create-project-cmd`) but running the commands above is usually sufficient for development.

## 6) Running the app (development)

To start the Laravel development server and other dev processes you can use the `dev` script from `composer` which uses `npx concurrently`:

```powershell
composer run-script dev
```

Or run individual services manually:

```powershell
php artisan serve
php artisan queue:listen --tries=1
```

## 7) Notes and security

- Never commit `.env` or any secret keys into source control.
- Use environment-specific secrets (Vault, GitHub Actions secrets, Azure Key Vault, etc.) in production.
- When sharing `APP_KEY` with a frontend that needs it for encrypt/decrypt, do so securely and rotate keys if they are exposed.

## 8) Coding standards and tools

- Do not install or use the `Mockery` package in this project. It has been observed to cause crashes and instability when used during local runs and tests. If you need to mock HTTP calls or dependencies, prefer framework-native tools (PHPUnit mocks, or HTTP client fakes provided by Laravel) that are proven stable with the project's stack.

- Use Laravel Pint to standardize code formatting. Pint enforces a consistent style and helps keep the codebase clean. Install and run Pint with Composer:

```powershell
composer require --dev laravel/pint
./vendor/bin/pint
```

- We recommend using the PSR-12 coding standard. Configure Pint or your IDE to use PSR-12 where possible. Pint can be configured via a `pint.json` or `pint.php` file in the project root.


## Troubleshooting

- If you see missing PHP extensions errors, install the required PHP extensions (OpenSSL, PDO, mbstring, tokenizer, xml, ctype, json, BCMath etc.).
- If migrations fail, confirm DB credentials in `.env` and that the database exists and is reachable.

## Example Nginx configuration

Below is a minimal example `server` block you can use to serve the backend via Nginx. Adjust `server_name`, `root`, and `fastcgi_pass` to match your production environment.

```nginx
server {
	listen 8080;
	server_name 165.22.240.22;
	root /var/www/exergame/be/public;

	index index.php;

	location / {
		try_files $uri $uri/ /index.php$is_args$args;
		autoindex on;
	}

	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		# fastcgi_pass php_upstream;
		fastcgi_pass unix:/run/php/php8.4-fpm.sock;
	}

	location ~ /\.ht {
		deny all;
	}
}
```

Notes:
- Ensure the PHP-FPM socket path is correct for your PHP version and OS. On some systems you may use `127.0.0.1:9000` instead of a unix socket.
- In production, remove `autoindex on` and configure proper `access_log` / `error_log` locations and TLS.

