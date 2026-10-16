# Deploy checklist

## Required variables
- DB_CONNECTION=mysql
- DB_HOST=${{MySQL.MYSQLHOST}}
- DB_PORT=${{MySQL.MYSQLPORT}}
- DB_DATABASE=${{MySQL.MYSQLDATABASE}}
- DB_USERNAME=${{MySQL.MYSQLUSER}}
- DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
- MYSQLHOST/PORT/DATABASE/USER/PASSWORD
  (same references)
- APP_KEY=base64:... (keep fixed)

## Networking
- Public domain target port: 8080
  (9000 is php-fpm, never use)

## Check after deploy
- No "No APP_KEY" warning in logs
- Site and /admin load
