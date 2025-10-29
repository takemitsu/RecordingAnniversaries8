# Passkey開発開始ガイド

このドキュメントは、Passkey（WebAuthn）機能の開発を開始する際のクイックリファレンスです。
詳細な実装手順は [plan.md](./plan.md) を参照してください。

---

## 📋 開発開始前チェックリスト

### 必須環境
- ✅ Docker Desktop が起動している
- ✅ .envの`APP_URL`が`http://localhost`になっている（デフォルト設定）

> **💡 重要**: WebAuthn/Passkeyは `http://localhost` で動作します。開発環境でHTTPS設定は不要です。

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

### Phase 1: 環境構築（所要時間: 5〜10分）

**目標:** Sail環境を起動し、アプリケーションが動作する状態にする

#### 1-1. Docker Desktop の起動確認

Docker Desktop が起動していることを確認してください。

#### 1-2. Sail環境の起動

```bash
./vendor/bin/sail up -d
```

#### 1-3. HTTP接続確認

ブラウザで `http://localhost` にアクセスして、アプリケーションが表示されることを確認。

> **💡 WebAuthnとHTTPS**: WebAuthn仕様により、`http://localhost` は安全なoriginとして扱われます。開発環境でHTTPS設定は不要です。本番環境のみHTTPSが必須です。

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
>>> DB::table('webauthn_credentials')->count();
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
4. DB確認: `DB::table('webauthn_credentials')->get()`

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
- ブラウザが古い/非対応
- `localhost` 以外のホスト名でアクセスしている

**解決方法:**
```bash
# ブラウザの対応確認（Chrome/Edge/Safari/Firefox最新版を使用）
# http://localhost でアクセスしているか確認
# 192.168.x.x などのIPアドレスでアクセスしている場合は localhost に変更
```

### 2. "Operation not permitted" エラー

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

### 3. Google OAuth認証が動かない

**原因:**
- GOOGLE_REDIRECT_URI の設定ミス

**解決方法:**
```bash
# .env を確認
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback

# Google Cloud Consoleで「認証済みのリダイレクトURI」を確認
# https://console.cloud.google.com/apis/credentials
# http://localhost/auth/google/callback が登録されているか確認
```

### 4. マイグレーションエラー

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

- [x] Phase 1: Sail環境起動・HTTP接続確認完了
- [x] Phase 2: パッケージインストール・マイグレーション完了
- [x] Phase 3: Passkey登録機能実装完了（動作確認は未実施）
- [ ] Phase 4: Passkey認証機能動作確認
- [ ] Phase 5: デバイス管理機能動作確認
- [ ] Phase 6: テスト全件PASS

---

最終更新: 2025-10-29
