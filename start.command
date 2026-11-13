#!/bin/bash
set -e

cd "$(dirname "$0")"

echo "=== Khoi dong MNM Bookstore ==="

for cmd in php composer npm; do
    if ! command -v "$cmd" >/dev/null 2>&1; then
        echo "Loi: Chua cai dat '$cmd'. Vui long cai dat truoc khi chay."
        read -r -p "Nhan Enter de dong..."
        exit 1
    fi
done

if [ ! -f ".env" ]; then
    echo "-> Tao file .env tu .env.example..."
    cp .env.example .env
fi

if grep -q "^APP_KEY=$" .env || ! grep -q "^APP_KEY=" .env; then
    echo "-> Tao APP_KEY..."
    php artisan key:generate --ansi
fi

if [ ! -d "vendor" ]; then
    echo "-> Cai dat thu vien PHP (composer install)..."
    composer install --no-interaction
fi

DB_CONN=$(grep -E "^DB_CONNECTION=" .env | tail -n 1 | cut -d '=' -f2 | tr -d '[:space:]')
if [ "$DB_CONN" = "sqlite" ] || [ -z "$DB_CONN" ]; then
    if [ ! -f "database/database.sqlite" ]; then
        echo "-> Khoi tao SQLite va du lieu mau..."
        touch database/database.sqlite
        php artisan migrate:fresh --seed --force
    else
        echo "-> Kiem tra migration database..."
        php artisan migrate --force
    fi
else
    echo "-> Kiem tra migration database..."
    php artisan migrate --force
fi

if [ ! -d "node_modules" ]; then
    echo "-> Cai dat thu vien Frontend (npm install)..."
    npm install --ignore-scripts
fi

echo "-> Bien dich giao dien (npm run build)..."
npm run build

if lsof -i :8000 -sTCP:LISTEN >/dev/null 2>&1; then
    echo "-> Phat hien server da dang chay o http://127.0.0.1:8000"
    open "http://127.0.0.1:8000"
    echo "Bam Ctrl+C hoac dong cua so neu muon thoat."
    exit 0
fi

echo "-> Mo trinh duyet tai http://127.0.0.1:8000"
(sleep 2 && open "http://127.0.0.1:8000") &

echo "-> Khoi chay Laravel server (Bam Ctrl+C de dung)..."
php artisan serve --host=127.0.0.1 --port=8000
