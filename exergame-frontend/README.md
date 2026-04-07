# Exergame (Frontend)

This folder contains the Nuxt 4 front-end for the Exergame project. Below are recommended versions, quick install steps, and important environment settings.

Recommended versions
- Node.js: 23.11.0
- pnpm: 10.8.0 (recommended) — this repo was tested with pnpm
- npm: 10.9.2 (alternative to pnpm)

If you use a Node version manager (nvm, n, Volta, fnm), pin Node to the recommended version to avoid compatibility issues.

Quick setup
## 1. Install dependencies:

- Using pnpm (recommended):

	`pnpm install`

- Using npm:

	`npm install`

## 2. Start the dev server:

	pnpm dev

## 3. Build for production:

	pnpm build

After building the app, it is recommended to use a process manager such as [PM2](https://pm2.keymetrics.io/) to run and manage your Nuxt project in production. PM2 helps keep your application running, handles restarts on crashes, and provides monitoring tools.

Example PM2 usage:

```bash
# To start the service:
pm2 start .output/server/index.mjs --name exergame-frontend

# To restart the service:
pm2 restart exergame-frontend

# To stop the service:
pm2 stop exergame-frontend
```

Refer to the [PM2 documentation](https://pm2.keymetrics.io/docs/usage/quick-start/) for more details on setup and configuration.

## 4. Preview the production build locally:

	pnpm preview

Scripts
- `pnpm dev` — start Nuxt dev server
- `pnpm build` — build for production
- `pnpm start` / `pnpm preview` — run the built app
- `pnpm lint` / `pnpm lint:fix` — run ESLint
- `pnpm test` — run unit tests with Vitest

Environment variables
- The project reads environment variables from `.env`.

- `NUXT_ENCRYPTION_KEY`
  - This key must match the encryption key used by the backend so the frontend can perform encryption/decryption operations that rely on a shared secret.
  - If your backend is Laravel, copy the `APP_KEY` value from the backend `.env` (for example `APP_KEY=base64:...`) and set the exact same value in `.env` as `NUXT_ENCRYPTION_KEY`.
  - Example in `.env`:

		NUXT_ENCRYPTION_KEY=base64:ixNzuo+rSrXWr+2POFRytEF9o2Mb+mqFkitbImSLp30=

Other variables
- NUXT_API_BASE_URL — base URL for the backend API (e.g. `http://api.exergame.test`)
- NUXT_SERVER_URL — Nuxt server URL

Notes & troubleshooting
- If you switch between package managers, remove `node_modules` and the pnpm lockfile before reinstalling.
- If you see environment-related errors, confirm the `NUXT_ENCRYPTION_KEY` in `.env` exactly matches the backend `APP_KEY`.

Editor (VS Code)
- Enable the ESLint and Prettier extensions in VS Code for consistent linting and formatting.

- Recommended extensions:
	- ESLint
	- Prettier

Nginx / Deployment
------------------

Use the following example Nginx server block as a reverse proxy for the Nuxt app. Replace `server_name` with your domain or server IP and ensure the proxy upstream points to the Nuxt server (default: `localhost:3000`).

```nginx
server {
	listen 80;
	server_name 165.22.240.22;

	location / {
		proxy_pass http://localhost:3000;
		proxy_http_version 1.1;
		proxy_set_header Upgrade $http_upgrade;
		proxy_set_header Connection 'upgrade';
		proxy_set_header Host $host;
		proxy_set_header X-Real-IP $remote_addr;
		proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
		proxy_set_header X-Forwarded-Proto $scheme;
		proxy_set_header X-Forwarded-Host $host;
		proxy_set_header X-Forwarded-Port $server_port;
		proxy_cache_bypass $http_upgrade;
		proxy_read_timeout 86400;
	}

	# Handle Nuxt dev server assets and HMR
	location ~ ^/(_nuxt|__nuxt__|__vite_ping|__vite|@fs|@id|node_modules|@vite)/ {
		proxy_pass http://localhost:3000;
		proxy_http_version 1.1;
		proxy_set_header Upgrade $http_upgrade;
		proxy_set_header Connection 'upgrade';
		proxy_set_header Host $host;
		proxy_set_header X-Real-IP $remote_addr;
		proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
		proxy_set_header X-Forwarded-Proto $scheme;
		proxy_cache_bypass $http_upgrade;
		proxy_read_timeout 86400;
	}

	error_page 404 /404.html;
	location = /404.html {
		internal;
	}

	location ~* \.(?:css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
		proxy_pass http://localhost:3000;
		proxy_set_header Host $host;
		proxy_set_header X-Real-IP $remote_addr;
		proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
		proxy_set_header X-Forwarded-Proto $scheme;
		expires 30d;
		access_log off;
	}

	location ~ /\.ht {
		deny all;
	}
}
```

