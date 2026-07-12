# slide-like

Laravel 13 アプリケーション。開発環境は [Laradock](https://laradock.io/)（Docker）で構築します。

このリポジトリには**アプリ本体（`src/`）のみ**が含まれます。Laradock 環境はリポジトリ外に置くため、下記「セットアップ」の手順で別途用意してください。

## 技術スタック

| 種別 | 内容 |
|------|------|
| フレームワーク | Laravel 13 (PHP 8.3) |
| Web サーバー | nginx |
| DB | MySQL 8.4 |
| 開発環境 | Laradock (Docker / Docker Compose) |

## ディレクトリ構成

Laradock 同梱の前提で、次の構成を想定しています。

```
slide-like/
├── laradock/   # Docker 開発環境（このリポジトリには含まれない／別途クローン）
└── src/        # このリポジトリ（Laravel アプリ本体）→ コンテナ内 /var/www にマウント
```

## 前提

- Docker / Docker Compose v2
- Git

## セットアップ（ゼロから再現する手順）

### 1. リポジトリを配置

```bash
mkdir -p ~/slide-like
git clone https://github.com/s-takahashi-hokkaido/slide-like.git ~/slide-like/src
```

### 2. Laradock を取得

```bash
git clone --depth 1 https://github.com/laradock/laradock.git ~/slide-like/laradock
cd ~/slide-like/laradock
cp .env.example .env
```

### 3. `laradock/.env` を編集

以下のキーを変更します（既存行を書き換え）。

```dotenv
APP_CODE_PATH_HOST=../src
DATA_PATH_HOST=~/.laradock/slide-like/data
COMPOSE_PROJECT_NAME=slide-like
PHP_VERSION=8.3
```

さらに `laradock/.env` の末尾に MySQL の設定を追記します（`mysql/defaults.env` を上書き）。

```dotenv
### MySQL (overrides mysql/defaults.env)
MYSQL_DATABASE=slide_like
MYSQL_USER=slide_like
MYSQL_PASSWORD=secret
MYSQL_ROOT_PASSWORD=root
MYSQL_PORT=3306
```

> nginx はデフォルトの `nginx/sites/default.conf` が `localhost` → `/var/www/public` を配信するため、`http://localhost` でそのまま動作します。
> `slide-like.test` で開きたい場合は `nginx/sites/slide-like.conf` を作成し（`server_name slide-like.test; root /var/www/public;`）、ホストの `hosts` に `127.0.0.1 slide-like.test` を追加します。

### 4. アプリの `.env` を用意

```bash
cd ~/slide-like/src
cp .env.example .env
```

`.env.example` の DB 設定は既にコンテナ向け（`DB_HOST=mysql` ほか）になっています。

### 5. コンテナを起動

```bash
cd ~/slide-like/laradock
docker compose up -d nginx mysql   # 初回は php-fpm / nginx / workspace のビルドで数分かかります
```

### 6. 依存インストール・キー生成・マイグレーション

workspace コンテナ内で実行します。

```bash
docker compose exec workspace bash
# --- 以下はコンテナ内 ---
composer install
php artisan key:generate
php artisan migrate
exit
```

### 7. 動作確認

- アプリ: http://localhost
- DB（ホストから）: `127.0.0.1:3306` / user `slide_like` / password `secret`（root は `root`）

## よく使うコマンド

すべて `~/slide-like/laradock` で実行します。

```bash
# 起動 / 停止 / 状態確認
docker compose up -d nginx mysql
docker compose stop
docker compose ps

# workspace に入って artisan / composer / npm を実行
docker compose exec workspace bash

# 単発実行の例
docker compose exec workspace php artisan migrate
docker compose exec workspace php artisan make:model Slide -m

# フロント（Vite HMR、ポート 5173 を公開済み）
docker compose exec workspace npm install
docker compose exec workspace npm run dev
```

## 補足

- `.env`・`vendor/`・`node_modules/` は Git 管理対象外です。
- DB 認証情報は開発用のデフォルト値です。本番では必ず変更してください。
