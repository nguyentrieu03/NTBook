Dev: docker compose up -d --build
Prod: docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build 
Env: .env
Port map: 8080, 8081, 443