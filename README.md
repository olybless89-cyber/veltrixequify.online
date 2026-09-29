# Veltrix Equify Platform

Official repository for **[Veltrix Equify](https://veltrixequify.online)** — High-Yield Investment & Capital Management Platform. Fully containerized, optimized for zero-downtime deployment on Railway, complete with internal Wallet Exchange, Profit Calculator, and automated Resend email delivery.

---

## 🚀 Key Features

- **Automated Railway Provisioning**: Zero-config deployment with native MySQL container linking.
- **Automated Resend API Emailing**: Built-in support for transactional emails via Resend (`smtp.resend.com`). Simply set `RESEND_API_KEY`.
- **Wallet Exchange & Reinvestment**: Seamless transfers between Interest Balance and Main Deposit Balance.
- **ROI & Investment Calculator**: Real-time investment forecasting for users and prospective investors.
- **Scheduler & Cron Automation**: Built-in Supervisor running Laravel Scheduler every minute for profit distribution and rank updates.
- **Multi-Theme Engine**: 6 responsive themes with unified navigation and user portal sidebars.

---

## 🛠️ Environment Configuration

Set the following environment variables on Railway:

| Variable | Description | Example |
|---|---|---|
| `APP_NAME` | Site Title | `Veltrix Equify` |
| `APP_URL` | Site URL | `https://veltrixequify.online` |
| `RESEND_API_KEY` | Resend API Key | `re_123456789...` |
| `MAIL_FROM_ADDRESS` | Sender Email | `support@veltrixequify.online` |
| `ADMIN_USERNAME` | Super Admin Username | `admin` |
| `ADMIN_PASSWORD` | Super Admin Password | `admin123456` |

---

## 📦 Deployment

This repository deploys directly to [Railway](https://railway.app):
1. Connect this GitHub repository (`olybless89-cyber/veltrixequify.online`).
2. Add a MySQL service to the project canvas.
3. Railway automatically builds and launches the Docker container with database migrations and seeds pre-loaded.
