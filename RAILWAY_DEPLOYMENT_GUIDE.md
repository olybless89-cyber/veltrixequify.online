# Matrix Platform - Railway Deployment Guide & Documentation

This repository has been fully configured and upgraded for seamless, zero-configuration deployment to **[Railway](https://railway.app)**, complete with end-to-end functionality, automated database provisioning, and modern HYIP/Matrix features.

---

## 🚀 What Was Upgraded & Built

### 1. Railway Docker Containerization
- **Multi-Service Architecture**: Configured PHP 8.1-FPM, high-performance Nginx, and Supervisord on lightweight Alpine Linux.
- **Dynamic Port Routing**: Automatically adapts to Railway's runtime `$PORT` variable without manual port mapping.
- **Zero-Downtime Health Checks**: Added `/health` probe endpoint for Railway's deployment health monitors.
- **Automated Startup Sequence (`docker-entrypoint.sh`)**:
  - Automatically provisions `.env` from `.env.example`.
  - Generates `APP_KEY` if missing.
  - Automatically tests database connectivity.
  - Detects if database tables are populated; if not, automatically executes `matrix:init-db` to import all 34 tables from `database/matrix_database.sql`.
  - Sets up storage directories and public asset symlinks.
  - Automatically runs the Laravel cron scheduler every minute for real-time investment ROI distributions and badge updates.

### 2. Auto-Provisioning Database Command (`php artisan matrix:init-db`)
- Connects to Railway MySQL using native Railway environment variables (`MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE` or `DATABASE_URL` / `MYSQL_URL`).
- Imports the complete database schema, default configurations, gateways, and frontend sections from [database/matrix_database.sql](file:///c:/Users/Prince%20DWO/Documents/matrix%20%282%29/database/matrix_database.sql).
- Creates or updates the default Super Administrator account.
- Safe on container restarts: automatically detects existing tables and skips re-importing to prevent data loss.

### 3. Critical PHP 8+ Compatibility Fixes
- **[FrontendController.php](file:///c:/Users/Prince%20DWO/Documents/matrix%20%282%29/app/Http/Controllers/FrontendController.php)**: Fixed `blogDetails` and `getLink` parameter signatures where optional parameters preceded required ones, preventing fatal errors on PHP 8+.
- **[BasicService.php](file:///c:/Users/Prince%20DWO/Documents/matrix%20%282%29/app/Services/BasicService.php)**: Fixed `makeTransaction` parameter order.
- **[Kernel.php](file:///c:/Users/Prince%20DWO/Documents/matrix%20%282%29/app/Console/Kernel.php)**: Fixed scheduler command signature from non-existent `cron:status` to `cron:run` scheduled every minute.
- **Dual Document Root Support**: Created [public/index.php](file:///c:/Users/Prince%20DWO/Documents/matrix%20%282%29/public/index.php) and [public/assets](file:///c:/Users/Prince%20DWO/Documents/matrix%20%282%29/public/assets) junction so the application securely runs under standard `/public` while remaining backward-compatible with legacy root setups.

### 4. New Functionality Added
1. **Wallet Exchange & Internal Transfer**:
   - Allows users to transfer funds between their **Interest Balance (Profit Wallet)** and **Main Balance (Deposit Wallet)**.
   - Enables instant reinvesting into investment plans without needing to withdraw to external gateways and redeposit.
   - Complete with interactive balance cards, max available auto-fill, password confirmation, double-entry transaction logging, and user notifications.
   - Route: `/user/wallet-exchange`
2. **Investment Profit & ROI Calculator**:
   - Allows prospective and existing investors to forecast their exact hourly, daily, and total returns before investing.
   - Interactive live calculation of Per Cycle Profit, Cycle Count, Total Gross Return, Capital Return status, and Net ROI %.
   - Public Page: `/calculator`
   - Real-time API: `/api/calculate-profit?plan_id={id}&amount={amount}`
   - Dynamic Theme Section: `@include($theme.'sections.calculate-profit')`
3. **Container Health & Status API**:
   - Endpoint: `/health` (returns JSON status of database connectivity, storage writeability, and service status for Railway monitoring).
4. **Security Hardening**:
   - Neutralized insecure vendor license telemetry checks that could trigger unintended directory deletions.

---

## 🛠️ How to Deploy to Railway (Step-by-Step)

### Option A: Deploy via GitHub (Recommended)

1. **Initialize Git & Push to GitHub**:
   ```bash
   git init
   git add .
   git commit -m "feat: railway deployment ready with wallet exchange and profit calculator"
   git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
   git branch -M main
   git push -u origin main
   ```

2. **Create New Project in Railway**:
   - Go to [Railway Dashboard](https://railway.app).
   - Click **+ New Project** -> **Deploy from GitHub repo**.
   - Select your repository.

3. **Add a MySQL Database**:
   - In the same project canvas, click **+ New** -> **Database** -> **Add MySQL**.
   - Railway will provision a MySQL instance and automatically link the environment variables:
     - `MYSQLHOST`
     - `MYSQLPORT`
     - `MYSQLUSER`
     - `MYSQLPASSWORD`
     - `MYSQLDATABASE`
     - `MYSQL_URL`
   - *Note: Our `config/database.php` has been configured to read these variables directly without needing manual renaming!*

4. **Add Custom Environment Variables (Optional)**:
   In your Railway App service settings under **Variables**, you can set:
   | Variable | Default Value | Description |
   |---|---|---|
   | `APP_ENV` | `production` | Application environment |
   | `APP_DEBUG` | `false` | Disable debug mode in production |
   | `APP_KEY` | *(Auto-generated)* | 32-character base64 application key |
   | `ADMIN_USERNAME` | `admin` | Default Admin username |
   | `ADMIN_EMAIL` | `admin@gmail.com` | Default Admin email |
   | `ADMIN_PASSWORD` | `admin123456` | Default Admin password |

5. **Generate Public Domain**:
   - Under your App service -> **Settings** -> **Networking** -> click **Generate Domain**.
   - Your site will be live at `https://your-domain.up.railway.app`!

---

### Option B: Deploy via Railway CLI

If you have the Railway CLI installed on your machine:
```bash
railway login
railway init
railway add --database mysql
railway up
```

---

## 🔐 Admin Access Credentials

Once deployed, visit your Railway URL:
- **Admin Panel URL**: `https://your-domain.railway.app/admin`
- **Username**: `admin` *(or value of `ADMIN_USERNAME`)*
- **Password**: `admin123456` *(or value of `ADMIN_PASSWORD`)*

> [!TIP]
> After logging in, navigate to **Basic Controls** in the Admin sidebar to update your site title, currency symbol, default theme, and payment gateway API keys.

---

## 💻 Local Testing with Docker (Optional)

To test the complete containerized stack locally before pushing:
```bash
docker-compose up -d --build
```
- Web Application: [http://localhost:8000](http://localhost:8000)
- Admin Login: [http://localhost:8000/admin](http://localhost:8000/admin)
- Health Check: [http://localhost:8000/health](http://localhost:8000/health)
- Profit Calculator: [http://localhost:8000/calculator](http://localhost:8000/calculator)
