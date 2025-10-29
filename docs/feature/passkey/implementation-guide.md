# Passkey実装ガイド（Laravel & 汎用）

このドキュメントは、将来Passkey（WebAuthn）を実装する際の実践的なガイドです。
このプロジェクトでの実装経験を基に、必須項目、ハマりどころ、ベストプラクティスをまとめています。

**対象読者:** Laravel開発者、または他の言語・フレームワークでPasskeyを実装する開発者

---

## 📚 目次

1. [WebAuthn/Passkeyとは](#webauthnpasskeyとは)
2. [実装前の準備](#実装前の準備)
3. [実装の流れ（6フェーズ）](#実装の流れ6フェーズ)
4. [ハマりやすいポイント](#ハマりやすいポイント)
5. [セキュリティ考慮事項](#セキュリティ考慮事項)
6. [テスト戦略](#テスト戦略)
7. [Laravel固有の注意点](#laravel固有の注意点)
8. [他言語への応用](#他言語への応用)

---

## WebAuthn/Passkeyとは

### 概要

- **WebAuthn**: W3C標準の公開鍵認証API
- **Passkey**: WebAuthnを使った、パスワード不要の認証方式の通称
- **仕組み**: 公開鍵暗号方式（秘密鍵はデバイスに保存、公開鍵はサーバーに保存）

### メリット

- フィッシング耐性（秘密鍵がサーバーに送信されない）
- パスワード不要（ユーザー体験の向上）
- 生体認証との連携（指紋、Face ID等）

### 対応環境

- **ブラウザ**: Chrome, Edge, Safari, Firefox（最新版）
- **OS**: Windows 10+, macOS, iOS, Android
- **開発環境**: `http://localhost` で動作（HTTPSは本番のみ必須）

---

## 実装前の準備

### 1. 必須チェックリスト

#### バックエンド
- [ ] PHPバージョン: 8.2以上（Laravel 11の場合）
- [ ] Composerパッケージ: `laragear/webauthn` またはサーバーサイドライブラリ
- [ ] データベース: WebAuthn Credentialsテーブル

#### フロントエンド
- [ ] ブラウザAPI対応確認: `navigator.credentials` の存在確認
- [ ] JavaScriptライブラリ: `@simplewebauthn/browser` 推奨
- [ ] トースト通知ライブラリ: `sonner` 等（ユーザーフィードバック用）

#### インフラ
- [ ] 本番環境: HTTPS必須
- [ ] 開発環境: `http://localhost` で動作（IPアドレス不可）
- [ ] ドメイン: Relying Party ID（通常はドメイン名）の決定

### 2. アーキテクチャの決定

#### パターン1: シンプルパターン（推奨: 個人開発・小規模）
```
Controller → Laragear/WebAuthnパッケージ → DB
```
- メリット: 実装が簡単、コード量が少ない
- デメリット: ビジネスロジックがControllerに集中

#### パターン2: Service層パターン（推奨: 中〜大規模）
```
Controller → Service → Repository → DB
```
- メリット: テスタビリティ向上、ロジックの再利用性
- デメリット: 実装コスト増

**このプロジェクトの選択**: パターン1（シンプルパターン）を採用

---

## 実装の流れ（6フェーズ）

### Phase 1: 環境構築（30分）

#### やること
1. 開発環境の起動確認
2. `http://localhost` でアクセスできることを確認
3. ブラウザの開発者ツールで `navigator.credentials` の存在を確認

#### コマンド例（Laravel Sail）
```bash
./vendor/bin/sail up -d
# ブラウザで http://localhost にアクセス
```

---

### Phase 2: パッケージインストール（30分）

#### やること
1. サーバーサイドライブラリのインストール
2. クライアントサイドライブラリのインストール
3. DBマイグレーション実行

#### コマンド例（Laravel）
```bash
# Composerパッケージ
./vendor/bin/sail composer require laragear/webauthn

# NPMパッケージ
./vendor/bin/sail npm install @simplewebauthn/browser sonner

# マイグレーション
./vendor/bin/sail artisan vendor:publish --tag=webauthn-migrations
./vendor/bin/sail artisan migrate
```

#### 確認ポイント
- `webauthn_credentials` テーブルが作成されている
- テーブル構造: `id`, `user_id`, `public_key`, `attestation_format`, `updated_at` 等

---

### Phase 3: 登録機能実装（3時間）

#### 実装するエンドポイント

1. **登録オプション取得**
   - `GET /webauthn/register/options`
   - レスポンス: Challenge, User ID, RP ID等

2. **登録完了**
   - `POST /webauthn/register`
   - リクエスト: Attestation Response（ブラウザから取得）
   - レスポンス: 成功/失敗

#### 実装するフロントエンド

1. **登録ボタン**
   - ユーザーのProfile画面等に配置
   - クリックで `navigator.credentials.create()` を呼び出し

2. **エラーハンドリング**
   - NotAllowedError: ユーザーキャンセル
   - TimeoutError: タイムアウト
   - SecurityError: セキュリティエラー（ドメイン不一致等）

#### 最小限のコード例（Laravel Controller）
```php
// 登録オプション取得
public function registerOptions(RegisterWebAuthnRequest $request): Responsable
{
    return $request->toCreate(); // Laragearが自動生成
}

// 登録完了
public function register(AttestedRequest $request): JsonResponse
{
    try {
        $request->save(); // DBに保存
        return response()->json(['message' => '登録成功']);
    } catch (\Exception $e) {
        Log::error('Passkey登録エラー', [...]);
        return response()->json(['message' => '登録失敗'], 500);
    }
}
```

#### 最小限のコード例（フロントエンド）
```typescript
import { startRegistration } from '@simplewebauthn/browser';

const handleRegister = async () => {
    try {
        // 1. オプション取得
        const optionsResponse = await axios.get('/webauthn/register/options');

        // 2. ブラウザAPIを呼び出し
        const credential = await startRegistration(optionsResponse.data);

        // 3. サーバーに送信
        await axios.post('/webauthn/register', credential);

        toast.success('パスキーを登録しました');
    } catch (error) {
        toast.error(getWebAuthnErrorMessage(error));
    }
};
```

---

### Phase 4: 認証機能実装（3時間）

#### 実装するエンドポイント

1. **認証オプション取得**
   - `GET /webauthn/login/options`
   - レスポンス: Challenge, RP ID等

2. **認証完了**
   - `POST /webauthn/login`
   - リクエスト: Assertion Response（ブラウザから取得）
   - レスポンス: セッション開始

#### 実装するフロントエンド

1. **ログインボタン**
   - ログイン画面に配置
   - クリックで `navigator.credentials.get()` を呼び出し

2. **セッション管理**
   - 認証成功後、通常のログイン処理と同じフローへ

#### セキュリティ実装（必須）

```php
public function login(AssertedRequest $request): JsonResponse
{
    try {
        $user = $request->login(); // Laragearが認証

        if (!$user) {
            return response()->json(['message' => '認証失敗'], 401);
        }

        // 🔐 重要: セッション固定攻撃対策
        $request->session()->regenerate();

        return response()->json(['message' => 'ログイン成功']);
    } catch (\Exception $e) {
        Log::error('Passkeyログインエラー', [...]);
        return response()->json(['message' => 'ログイン失敗'], 500);
    }
}
```

---

### Phase 5: 管理機能実装（2時間）

#### 実装する機能

1. **デバイス一覧表示**
   - 登録済みPasskeyのリスト
   - デバイス名、最終使用日時を表示

2. **デバイス削除**
   - 個別のPasskeyを削除
   - **重要**: 最後のPasskey削除制限（後述）

#### 最後のPasskey削除制限（セキュリティ上重要）

```php
public function destroy(Request $request, string $id): JsonResponse
{
    $user = $request->user();
    $credential = $user->webAuthnCredentials()->findOrFail($id);

    // 最後のPasskeyかチェック
    if ($user->webAuthnCredentials()->count() === 1) {
        // 他の認証方法があるか確認
        $hasPassword = !is_null($user->password);
        $hasOAuth = !is_null($user->google_id);

        if (!$hasPassword && !$hasOAuth) {
            return response()->json([
                'message' => '最後のPasskeyは削除できません'
            ], 403);
        }
    }

    $credential->delete();
    return response()->json(['message' => '削除しました']);
}
```

**理由**: ユーザーがログイン手段を失うのを防ぐ（ロックアウト防止）

---

### Phase 6: テスト・仕上げ（4時間）

#### 6.1 テストコード作成（必須）

**Feature Test（統合テスト）**
```php
// tests/Feature/Auth/WebAuthnTest.php

public function test_authenticated_user_can_get_registration_options()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson('/webauthn/register/options');

    $response->assertStatus(200)
        ->assertJsonStructure(['publicKey']);
}

public function test_user_cannot_delete_last_credential_without_backup_auth()
{
    $user = User::factory()->create([
        'password' => null,
        'google_id' => null,
    ]);
    $credential = $this->createCredential($user);

    $response = $this->actingAs($user)
        ->deleteJson("/webauthn/credentials/{$credential->id}");

    $response->assertStatus(403);
}
```

**テスト項目（最低限）**
- ✅ 登録オプション取得（認証済みユーザーのみ）
- ✅ 認証オプション取得（ゲストユーザーのみ）
- ✅ デバイス一覧取得（自分のデバイスのみ表示）
- ✅ デバイス削除（自分のデバイスのみ削除可能）
- ✅ 最後のPasskey削除制限
- ✅ 存在しないPasskey削除時の404エラー

#### 6.2 エラーハンドリング強化（必須: 個人開発でも）

**バックエンド: try-catch + ログ記録**
```php
try {
    $request->save();
    return response()->json(['message' => '成功']);
} catch (\Exception $e) {
    Log::error('エラー', [
        'user_id' => $request->user()?->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    return response()->json(['message' => '失敗'], 500);
}
```

**フロントエンド: エラー分類**
```typescript
export function getWebAuthnErrorMessage(error: Error): string {
    if (error.name === 'NotAllowedError') {
        return 'キャンセルされました';
    }
    if (error.name === 'TimeoutError') {
        return 'タイムアウトしました';
    }
    if (error.name === 'SecurityError') {
        return 'セキュリティエラーが発生しました';
    }
    if (error.name === 'InvalidStateError') {
        return 'このデバイスは既に登録されています';
    }
    return error.message || '不明なエラーが発生しました';
}
```

#### 6.3 国際化（オプション: 個人開発では不要）

多言語対応が必要な場合のみ実装。

#### 6.4 Notification（オプション: 企業開発推奨）

セキュリティ強化のため、Passkey登録時にメール通知を送る。

---

## ハマりやすいポイント

### 1. 🔴 開発環境のURL問題

**問題**: IPアドレス（`http://192.168.x.x`）でアクセスするとWebAuthnが動かない

**解決**: 必ず `http://localhost` を使う
```bash
# NG
http://192.168.1.100:3000

# OK
http://localhost:3000
```

**理由**: WebAuthn仕様で `localhost` のみが安全なoriginとして認められている

---

### 2. 🔴 Relying Party ID（RP ID）の設定

**問題**: 本番環境でRP IDの設定ミスによりPasskeyが動かない

**解決**: 環境ごとに正しいRP IDを設定
```env
# .env
WEBAUTHN_RELYING_PARTY_ID=localhost  # 開発環境
WEBAUTHN_RELYING_PARTY_ID=example.com  # 本番環境
```

**注意**:
- サブドメイン間で共有したい場合: `example.com` を指定
- サブドメイン固有にしたい場合: `app.example.com` を指定

---

### 3. 🔴 最後のPasskey削除によるロックアウト

**問題**: ユーザーが最後のPasskeyを削除し、ログインできなくなる

**解決**: Phase 5で示したように、最後のPasskey削除を制限する

**追加考慮**:
- リカバリーコードの発行（推奨）
- 管理者による強制リセット機能

---

### 4. 🟡 ブラウザごとの挙動の違い

**問題**: Safariでのみエラーが発生する等

**解決**: ブラウザごとのテストを実施
- Chrome/Edge: Windows Hello, Touch ID
- Safari: Touch ID, Face ID
- Firefox: Windows Hello

**デバッグ方法**:
```javascript
// ブラウザの対応確認
if (!window.PublicKeyCredential) {
    alert('このブラウザはPasskeyに対応していません');
}

// Platform Authenticatorの対応確認
const available = await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
if (!available) {
    alert('このデバイスは生体認証に対応していません');
}
```

---

### 5. 🟡 テストでのWebAuthnモック

**問題**: 自動テストでWebAuthn APIを呼び出せない

**解決**: Laragear/WebAuthnの場合、モックは不要（Form Requestが自動的にモックデータを扱う）

**他の言語の場合**: モックライブラリを使用
```javascript
// Jest等でモック
global.navigator.credentials = {
    create: jest.fn().mockResolvedValue(mockCredential),
    get: jest.fn().mockResolvedValue(mockAssertion),
};
```

---

## セキュリティ考慮事項

### 1. 🔐 必須セキュリティ対策

#### セッション固定攻撃対策
```php
$request->session()->regenerate(); // ログイン成功後に必ず実行
```

#### CSRF保護
- Laravel: 自動的に保護される（`csrf_token()` を使用）
- 他の言語: CSRFトークンの検証を実装

#### Rate Limiting（レート制限）
```php
// routes/web.php
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/webauthn/register', ...);
    Route::post('/webauthn/login', ...);
});
```

---

### 2. 🔐 推奨セキュリティ対策

#### User Verification（ユーザー検証）の要求
```php
// config/webauthn.php
'user_verification' => 'required', // または 'preferred'
```

**設定値**:
- `required`: 必ず生体認証を要求（推奨: 金融系アプリ）
- `preferred`: 可能なら生体認証を要求（推奨: 一般アプリ）
- `discouraged`: 生体認証不要（推奨しない）

#### Attestation（構成証明）の検証
```php
'attestation_conveyance' => 'none', // 個人開発は 'none' でOK
// 'direct' または 'indirect': デバイスの真正性を検証（企業向け）
```

---

### 3. 🔐 監査ログ

**重要操作のログ記録**:
- Passkey登録
- Passkeyでのログイン
- Passkey削除

```php
Log::info('Passkey登録', [
    'user_id' => $user->id,
    'credential_id' => $credential->id,
    'user_agent' => $request->userAgent(),
    'ip_address' => $request->ip(),
]);
```

---

## テスト戦略

### 1. 単体テスト（Unit Test）

**対象**: Service層、Enum等のビジネスロジック

**スキップ可能なケース**: シンプルパターンを採用した場合

---

### 2. 統合テスト（Feature Test）

**必須テスト項目**:
```
✅ 認証済みユーザーのみ登録できる
✅ ゲストユーザーのみ認証オプション取得できる
✅ 自分のPasskeyのみ表示・削除できる
✅ 最後のPasskey削除が制限される
✅ 存在しないPasskey削除時に404エラー
✅ 一覧が更新日時順に表示される
```

**テスト実行**:
```bash
./vendor/bin/sail artisan test --filter=WebAuthn
```

---

### 3. E2Eテスト（推奨: 企業開発）

**ツール**: Playwright, Cypress, Selenium

**テスト項目**:
- 実際のブラウザでPasskey登録・認証ができる
- エラーメッセージが表示される
- デバイス一覧が正しく表示される

**注意**: WebAuthn APIのモックが複雑なため、E2Eテストは本番に近い環境で実施

---

## Laravel固有の注意点

### 1. Laragear/WebAuthnパッケージの活用

**メリット**:
- マイグレーション自動生成
- Form Requestでの自動検証
- `WebAuthnAuthenticatable` Traitによる簡単な統合

**使い方**:
```php
// app/Models/User.php
use Laragear\WebAuthn\WebAuthnAuthenticatable;

class User extends Authenticatable
{
    use WebAuthnAuthenticatable;
}
```

---

### 2. Inertia.jsでの実装

**Propsの受け渡し**:
```php
// Controller
return Inertia::render('Profile/Edit', [
    'credentials' => WebAuthnCredentialResource::collection(
        $request->user()->webAuthnCredentials
    ),
]);
```

**フロントエンド**:
```tsx
import { router } from '@inertiajs/react';

const handleDelete = (id: string) => {
    router.delete(`/webauthn/credentials/${id}`, {
        onSuccess: () => toast.success('削除しました'),
    });
};
```

---

### 3. Laravel Sailでの開発

**コマンド実行**:
```bash
# エイリアス設定推奨
alias sail='./vendor/bin/sail'

# よく使うコマンド
sail up -d
sail artisan test
sail pint
sail npm run dev
```

---

## 他言語への応用

### Node.js + Express

**サーバーサイドライブラリ**: `@simplewebauthn/server`

```javascript
const { generateRegistrationOptions, verifyRegistrationResponse } = require('@simplewebauthn/server');

app.get('/webauthn/register/options', async (req, res) => {
    const options = await generateRegistrationOptions({
        rpName: 'My App',
        rpID: 'localhost',
        userID: req.user.id,
        userName: req.user.email,
    });

    req.session.challenge = options.challenge;
    res.json(options);
});
```

---

### Python + Django/Flask

**サーバーサイドライブラリ**: `webauthn` または `py_webauthn`

```python
from webauthn import generate_registration_options

@login_required
def register_options(request):
    options = generate_registration_options(
        rp_id='localhost',
        rp_name='My App',
        user_id=str(request.user.id),
        user_name=request.user.email,
    )

    request.session['challenge'] = options.challenge
    return JsonResponse(options.dict())
```

---

### Ruby on Rails

**サーバーサイドライブラリ**: `webauthn` gem

```ruby
class WebauthnController < ApplicationController
  def register_options
    options = WebAuthn::Credential.options_for_create(
      user: { id: current_user.id, name: current_user.email },
      exclude: current_user.webauthn_credentials.pluck(:external_id)
    )

    session[:challenge] = options.challenge
    render json: options
  end
end
```

---

## まとめ: 実装チェックリスト

### Phase 1: 環境構築
- [ ] 開発環境が `http://localhost` で動作
- [ ] ブラウザが WebAuthn 対応

### Phase 2: パッケージインストール
- [ ] サーバーサイドライブラリインストール
- [ ] クライアントサイドライブラリインストール
- [ ] DBマイグレーション実行

### Phase 3: 登録機能
- [ ] 登録オプション取得エンドポイント
- [ ] 登録完了エンドポイント
- [ ] フロントエンド実装
- [ ] エラーハンドリング

### Phase 4: 認証機能
- [ ] 認証オプション取得エンドポイント
- [ ] 認証完了エンドポイント
- [ ] セッション再生成（セキュリティ対策）
- [ ] フロントエンド実装

### Phase 5: 管理機能
- [ ] デバイス一覧表示
- [ ] デバイス削除
- [ ] 最後のPasskey削除制限

### Phase 6: テスト・仕上げ
- [ ] Feature Test（最低16テスト）
- [ ] エラーハンドリング強化（try-catch + ログ）
- [ ] Rate Limiting設定
- [ ] 監査ログ実装

### セキュリティチェック
- [ ] セッション固定攻撃対策
- [ ] CSRF保護
- [ ] Rate Limiting
- [ ] User Verification設定
- [ ] 監査ログ

---

## 参考リンク

### 公式ドキュメント
- [WebAuthn Guide](https://webauthn.guide/) - WebAuthnの仕組みを図解で解説
- [Laragear/WebAuthn](https://github.com/Laragear/WebAuthn) - Laravel用パッケージ
- [SimpleWebAuthn](https://simplewebauthn.dev/) - クライアント/サーバーライブラリ
- [MDN: Web Authentication API](https://developer.mozilla.org/en-US/docs/Web/API/Web_Authentication_API)

### このプロジェクトのドキュメント
- [plan.md](./plan.md) - 詳細な実装計画
- [SETUP.md](./SETUP.md) - 開発開始ガイド

---

**最終更新**: 2025-10-29
**作成者**: このプロジェクトでの実装経験を基に作成
