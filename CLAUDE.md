# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 11 + React + TypeScript application for tracking anniversaries and important dates. Users can organize their memorable dates into categories (entities) and get countdown/count-up information for each anniversary.

**Key Technologies:**
- Backend: Laravel 11 with PHP 8.2+
- Frontend: React 18 + TypeScript + Vite
- UI: Tailwind CSS + Headless UI
- Authentication: Laravel Breeze + Google OAuth (Socialite)
- Database: SQLite (development)
- Testing: PHPUnit

## Data Architecture

The application follows a three-tier data model:
- **Users** → **Entities** (anniversary categories/groups) → **Days** (individual anniversary dates)

**Models:**
- `User`: Authentication with Google OAuth support (`google_id` field, nullable password)
- `Entity`: Groups/categories for organizing anniversaries (belongs to User)
- `Day`: Individual anniversary dates with smart date calculations (belongs to Entity)

Both `Entity` and `Day` models use soft deletes for data preservation.

## Common Development Commands

### Backend (Laravel/PHP)
```bash
# 開発サーバー起動
php artisan serve

# マイグレーション実行
php artisan migrate

# データベースシード実行
php artisan db:seed

# テスト実行
./vendor/bin/phpunit
# または: php artisan test

# Laravel Pint でコード整形
./vendor/bin/pint

# アプリケーションキャッシュクリア
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Frontend (React/TypeScript)
```bash
# Vite 開発サーバー起動
npm run dev

# プロダクション用ビルド（TypeScript コンパイルと SSR を含む）
npm run build

# TypeScript 型チェック
npx tsc --noEmit
```

### 開発環境セットアップ
```bash
# PHP 依存関係インストール
composer install

# Node 依存関係インストール
npm install

# 環境ファイル設定
cp .env.example .env
php artisan key:generate

# マイグレーションとシードデータ実行
php artisan migrate --seed

# Laravel と Vite サーバーを起動（別々のターミナルで実行）
php artisan serve
npm run dev
```

## Application Architecture

**Frontend Structure:**
- `resources/js/Pages/`: Inertia.js page components
- `resources/js/Components/`: Reusable React components
- `resources/js/Layouts/`: Layout components (AuthenticatedLayout, GuestLayout)
- `resources/js/types/`: TypeScript type definitions
- `resources/js/util/`: Utility functions (including `japanDate.tsx` for date calculations)

**Backend Structure:**
- Standard Laravel structure with Inertia.js integration
- Controllers in `app/Http/Controllers/`: DaysController, EntitiesController, etc.
- Models use standard Eloquent relationships and soft deletes
- Authentication via Laravel Breeze with Google OAuth integration

**Key Features:**
- Smart anniversary date calculations (handles future/past dates, annual recurrence)
- Japanese date utilities for cultural anniversary tracking
- Soft deletion preserves data while allowing "removal"
- Google OAuth integration for seamless authentication

## Testing

- PHPUnit configuration in `phpunit.xml`
- Test files in `tests/Feature/` and `tests/Unit/`
- Includes authentication tests for both standard and OAuth flows
- Database configured for testing environment

## Deployment Notes

- Uses Vite for asset bundling with SSR support
- Laravel Sanctum for API authentication
- Configured for both development and production environments
- Docker Compose available (`docker-compose.yml`)