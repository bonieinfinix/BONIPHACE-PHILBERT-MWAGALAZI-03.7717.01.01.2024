[README.md](https://github.com/user-attachments/files/25170650/README.md)[Uploading REAFood Ordering System (Tanzanian Local Foods)

Overview
- A simple full-stack food ordering web app using PHP + MySQL backend and vanilla JavaScript frontend.
- Seeded dishes: Wali Maharage, Wali Kuku, Ugali Nyama, Ugali Dagaa, Chapati, Mandazi (prices in TZS).
- Core features: user registration/login, role-based admin, CRUD for foods (admin), search/filter, cart and orders, input validation, password hashing.

Quick Setup

1) Create the database and import the schema

Open a MySQL client (MySQL shell, phpMyAdmin, MySQL Workbench) and run:

```sql
CREATE DATABASE IF NOT EXISTS food_ordering DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE food_ordering;
-- then import the provided file `db.sql` (in the project root)
SOURCE db.sql;
```

2) Configure database credentials

Edit `api/db.php` and set `$DB_HOST`, `$DB_NAME`, `$DB_USER`, `$DB_PASS` to match your MySQL server.

3) Serve the app

Option A — quick PHP built-in server (for development):

```bash
# from project root (Windows PowerShell/CMD)
cd public
php -S 127.0.0.1:8000
# then open http://127.0.0.1:8000 in your browser
```

Option B — use XAMPP/WAMP/IIS

- Point your document root to the `public` folder or copy contents into your web server's www directory.
- Ensure PHP sessions and PDO MySQL extensions are enabled.

4) Create an admin user (optional)

- Register a normal user via the UI, then in MySQL promote the user to admin:

```sql
UPDATE users SET role='admin' WHERE email='your-user-email@example.com';
```

Testing checklist

- Register a user: open `/register.html` and create account.
- Login: `/login.html`.
- Browse menu and add items to cart on `/index.html`.
- View cart and place order: `/cart.html` (you must be logged in to place an order).
- As admin (after promoting a user), open `/admin.html` to create/edit/delete foods and view orders.

Files of interest

- API endpoints: [api/db.php](api/db.php), [api/auth.php](api/auth.php), [api/foods.php](api/foods.php), [api/orders.php](api/orders.php), [api/admin.php](api/admin.php)
- Frontend: [public/index.html](public/index.html), [public/cart.html](public/cart.html), [public/admin.html](public/admin.html), JS in [public/js](public/js)
- DB schema + seed: [db.sql](db.sql)

Security notes

- Passwords are stored using `password_hash()` (bcrypt).
- All DB queries use prepared statements to avoid SQL injection.
- Basic server-side input validation is implemented; for production, add stricter validation and HTTPS.

If you want, I can:

- Add file upload handling for real images and store uploads in `public/images`.
- Wire up advanced filters, pagination, or payment integration.

Commands summary

```bash
# import DB (MySQL client)
mysql -u root -p < db.sql

# run built-in PHP server for dev (from /public)
php -S 127.0.0.1:8000
```

Enjoy — tell me if you want deployment help or extra features.
 
Permanent deployment (Docker / Railway / DigitalOcean)

This project includes a `Dockerfile` and `docker-compose.yml` to run the app in containers and deploy to container platforms.

Local container test (Docker)

```bash
# build and run local containers
docker-compose up --build
# open http://127.0.0.1:8000
```

Deploy to a permanent host (recommended flow using GitHub + Railway)

1. Create a GitHub repository and push the project (root contains `Dockerfile`).
2. Sign up at https://railway.app and create a new project -> "Deploy from GitHub" and select your repo.
3. Add a MySQL plugin in Railway (Project -> Plugins -> MySQL) — Railway will create connection details.
4. In Railway service settings, add the environment variables: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` using the credentials from the MySQL plugin.
5. Deploy — Railway will build the Docker image and provide a permanent URL.

Alternative hosts
- DigitalOcean App Platform: connect your GitHub repo, configure a service using the `Dockerfile`, and add a Managed MySQL database. Set the same `DB_*` environment variables.
- Railway and Render are developer-friendly; choose a host that supports Docker and a managed MySQL instance (or provide external MySQL credentials).

Notes
- `api/db.php` now reads DB credentials from `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` environment variables for easy configuration on hosts.
- After deployment, import `db.sql` into the managed MySQL instance (use the host, user, and password provided by the host).

If you want, I can prepare a GitHub Actions workflow to auto-deploy on push, or create a one-click Render/Railway button config — which would you prefer?
DME.md…]()
