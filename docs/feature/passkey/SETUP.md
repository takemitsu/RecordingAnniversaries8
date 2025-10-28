# Passkey開発開始ガイド

このドキュメントは、Passkey（WebAuthn）機能の開発を開始する際のクイックリファレンスです。
詳細な実装手順は [plan.md](./plan.md) を参照してください。

---

## 📋 開発開始前チェックリスト

### 必須環境
- ✅ Docker Desktop が起動している
- ⚠️ mkcert がインストールされている（`brew install mkcert`）
- ⚠️ SSL証明書が生成されている（`.docker/ssl/`ディレクトリ）
- ⚠️ docker-compose.ymlにSSL設定が追加されている
- ⚠️ .envの`APP_URL`が`https://localhost`になっている

### パッケージ状況
- ❌ `laragear/webauthn` 未インストール
- ❌ `@simplewebauthn/browser` 未インストール
- ❌ `sonner` (Toast) 未インストール

### データバックアップ（推奨）
マイグレーション実行前に既存データをバックアップしてください。

```bash
# SQLiteの場合
cp database/database.sqlite database/database.sqlite.backup.$(date +%Y%m%d)
```

---

## 🚀 段階的実装ガイド

### Phase 1: 環境構築（所要時間: 30分〜1時間）

**目標:** HTTPS環境でアプリケーションが動作する状態にする

#### 1-1. SSL証明書の生成

```bash
# mkcertのインストール（未インストールの場合）
brew install mkcert
mkcert -install

# SSL証明書の生成
mkdir -p .docker/ssl
cd .docker/ssl
mkcert -cert-file cert.pem -key-file key.pem localhost 127.0.0.1 ::1
cd ../..
```

#### 1-2. docker-compose.ymlの更新

`docker-compose.yml`の`laravel.test`サービスに以下を追加：

```yaml
services:
    laravel.test:
        volumes:
            - '.:/var/www/html'
            - './.docker/ssl:/etc/ssl/private'  # 追加
        environment:
            WWWUSER: '${WWWUSER}'
            LARAVEL_SAIL: 1
            SSL_CERT: '/etc/ssl/private/cert.pem'  # 追加
            SSL_KEY: '/etc/ssl/private/key.pem'    # 追加
        ports:
            - '${APP_PORT:-80}:80'
            - '${APP_SSL_PORT:-443}:443'  # 追加
```

#### 1-3. .envの更新

```bash
# .envファイルを編集
APP_URL=https://localhost
```

#### 1-4. Sail環境の再起動

```bash
./vendor/bin/sail down
./vendor/bin/sail up -d
```

#### 1-5. HTTPS接続確認

ブラウザで `https://localhost` にアクセスして、SSL証明書エラーが出ないことを確認。

---

### Phase 2: パッケージインストールとマイグレーション（所要時間: 15分）

**目標:** WebAuthn関連のパッケージとDBテーブルを準備

#### 2-1. Composerパッケージのインストール

```bash
./vendor/bin/sail composer require laragear/webauthn
```

#### 2-2. NPMパッケージのインストール

```bash
./vendor/bin/sail npm install @simplewebauthn/browser sonner
```

#### 2-3. マイグレーションの実行

```bash
# Laragear/WebAuthnのマイグレーションを公開
./vendor/bin/sail artisan vendor:publish --tag=webauthn-migrations

# マイグレーション実行
./vendor/bin/sail artisan migrate
```

#### 2-4. 動作確認

```bash
# テーブルが作成されたか確認
./vendor/bin/sail artisan tinker
>>> DB::table('web_authn_credentials')->count();
=> 0  # 0件であることを確認
>>> exit
```

---

### Phase 3: 登録機能実装（所要時間: 2〜3時間）

**目標:** 既存ユーザーがPasskeyを登録できる

#### 実装するファイル（plan.md参照）
1. Enum（WebAuthnAction, WebAuthnStatus）
2. Service（WebAuthnService）
3. Controller（WebAuthnController - 登録部分のみ）
4. FormRequest（RegisterWebAuthnRequest）
5. Frontend（RegisterPasskey.tsx）
6. Routes（web.php）

#### 動作確認方法
1. Profile画面にPasskey登録ボタンが表示される
2. ボタンをクリックして指紋認証/Face IDが起動する
3. 認証後にデバイスが登録される
4. DB確認: `DB::table('web_authn_credentials')->get()`

---

### Phase 4: 認証機能実装（所要時間: 2〜3時間）

**目標:** Passkeyでログインできる

#### 実装するファイル（plan.md参照）
1. Controller（WebAuthnController - 認証部分）
2. FormRequest（AuthenticateWebAuthnRequest）
3. Frontend（LoginWithPasskey.tsx）
4. Routes（web.php）

#### 動作確認方法
1. ログアウト
2. ログイン画面に「Passkeyでログイン」ボタンが表示される
3. ボタンをクリックして認証
4. ダッシュボードにリダイレクトされる

---

### Phase 5: 管理機能実装（所要時間: 1〜2時間）

**目標:** 登録済みデバイスの一覧表示・削除

#### 実装するファイル（plan.md参照）
1. Controller（WebAuthnController - 一覧・削除）
2. Resource（WebAuthnCredentialResource）
3. Frontend（PasskeyList.tsx, PasskeyItem.tsx）

#### 動作確認方法
1. Profile画面でPasskey一覧が表示される
2. デバイス名、最終使用日時が表示される
3. 削除ボタンで削除できる

---

### Phase 6: テスト・仕上げ（所要時間: 2〜3時間）

**目標:** 本番環境に近い品質にする

#### 実装項目
1. テストコード作成（plan.md参照）
   - Feature Test（Registration, Authentication, Management）
   - Unit Test（Service, Enum）
2. エラーハンドリング
3. 国際化（i18n）
4. Notification（Passkey登録通知）

#### テスト実行

```bash
# 全テスト実行
./vendor/bin/sail artisan test

# 特定のテストのみ実行
./vendor/bin/sail artisan test --filter=WebAuthn
```

---

## 🔧 トラブルシューティング

### 1. "WebAuthn is not supported" エラー

**原因:**
- HTTPでアクセスしている
- ブラウザが古い/非対応

**解決方法:**
```bash
# HTTPSでアクセスしているか確認
# ブラウザのURLバーを確認: https://localhost

# ブラウザの対応確認（Chrome/Edge/Safari/Firefox最新版を使用）
```

### 2. SSL証明書エラー

**原因:**
- mkcert -install が実行されていない
- ブラウザキャッシュ

**解決方法:**
```bash
# mkcertの再インストール
mkcert -install

# ブラウザを完全に再起動
```

### 3. "Operation not permitted" エラー

**原因:**
- Sailコンテナ外でコマンドを実行している

**解決方法:**
```bash
# 必ず ./vendor/bin/sail を使う
./vendor/bin/sail artisan migrate

# エイリアスを設定すると便利
alias sail='./vendor/bin/sail'
sail artisan migrate
```

### 4. Google OAuth認証が動かない

**原因:**
- GOOGLE_REDIRECT_URI がHTTPのまま

**解決方法:**
```bash
# .env を更新
GOOGLE_REDIRECT_URI=https://localhost/auth/google/callback

# Google Cloud Consoleで「認証済みのリダイレクトURI」を更新
# https://console.cloud.google.com/apis/credentials
```

### 5. マイグレーションエラー

**原因:**
- SQLiteとMySQLの混在
- 既存テーブルとの競合

**解決方法:**
```bash
# 現在のDB接続確認
./vendor/bin/sail artisan tinker
>>> config('database.default')

# マイグレーションをロールバック
./vendor/bin/sail artisan migrate:rollback

# 再実行
./vendor/bin/sail artisan migrate
```

---

## 📚 参考リンク

- **詳細な実装手順:** [plan.md](./plan.md)
- **Laragear/WebAuthn公式:** https://github.com/Laragear/WebAuthn
- **WebAuthn仕様:** https://webauthn.guide/
- **@simplewebauthn/browser:** https://simplewebauthn.dev/

---

## 💡 開発のコツ

### コミット前チェック

```bash
# PHP静的解析
./vendor/bin/sail composer pstan

# PHPコードフォーマット
./vendor/bin/sail pint

# TypeScript/Reactフォーマット
./vendor/bin/sail npx biome check --write
```

### 段階的にコミット

Phase単位でコミットすることで、問題発生時にロールバックしやすくなります。

```bash
# Phase 1完了後
git add .
git commit -m "feat(passkey): Phase 1 - 環境構築完了（HTTPS対応）"

# Phase 2完了後
git add .
git commit -m "feat(passkey): Phase 2 - パッケージインストール完了"
```

### ブラウザの開発者ツールを活用

WebAuthn APIの動作確認：
- Console: `navigator.credentials` が存在するか確認
- Network: `/webauthn/register/options` などのAPIレスポンスを確認

---

## 📝 メモ

### 既存コードとの統合確認事項

- Userモデルの`$fillable`に`google_id`を追加（plan.mdで対応済み）
- 現在のDB接続: SQLite（docker-compose.ymlはMySQL設定）
- 既存認証: Laravel Breeze + Google OAuth

### Phase完了チェックリスト

- [ ] Phase 1: HTTPS接続確認完了
- [ ] Phase 2: パッケージインストール・マイグレーション完了
- [ ] Phase 3: Passkey登録機能動作確認
- [ ] Phase 4: Passkey認証機能動作確認
- [ ] Phase 5: デバイス管理機能動作確認
- [ ] Phase 6: テスト全件PASS

---

最終更新: 2025-10-28
