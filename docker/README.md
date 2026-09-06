Dev: docker compose up -d --build

Composer (khong can cai local):
  docker compose exec app composer require vendor/package
  docker compose exec app composer install
  docker compose exec app php artisan migrate

Reset (fix MySQL / stale container): 
  powershell -ExecutionPolicy Bypass -File docker/reset.ps1
  # hoặc thủ công:
  docker compose down --remove-orphans
  docker rm -f ntbook_mysql ntbook_app ntbook_nginx ntbook_redis ntbook_phpmyadmin
  docker volume rm ssp_mysql_data ntbook_mysql_data
  docker compose up -d --force-recreate --build

Prod: docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build 
Env: .env
Port map: 8080, 8081, 443

Luu y MySQL:
- DB_USERNAME trong .env KHONG duoc la "root" (dung ntbook)
- Neu loi "no healthcheck configured" => container cu, chay reset o tren