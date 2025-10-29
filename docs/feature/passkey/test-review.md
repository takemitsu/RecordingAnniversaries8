# WebAuthn テスト品質レビューと改善計画

**日付**: 2025-10-29
**Phase**: 6.1 テストの作成
**現在のスコア**: 6.5/10
**目標スコア**: 8.5/10

---

## 1. 現状評価サマリー

| カテゴリ | スコア | 評価 |
|---------|-------|------|
| 網羅性 | 6/10 | 基本機能はOKだが、JSONレスポンス検証や実際の登録・ログインフローが不足 |
| 堅牢性 | 7/10 | セキュリティ基本は良好だが、エッジケースとビジネスロジックテストが不足 |
| 標準準拠 | 7/10 | Laravel標準は概ね遵守だが、プロジェクト内一貫性に欠ける |
| Factory使用 | 6/10 | Laragear/WebAuthnはFactoryをサポートしていないため、`unguarded()`使用は現状では適切 |
| **総合** | **6.5/10** | |

---

## 2. 現在実装されているテスト

### tests/Feature/Auth/WebAuthnTest.php (9テスト)

1. ✅ `test_authenticated_user_can_get_registration_options` - 認証済みユーザーが登録オプションを取得できる
2. ✅ `test_guest_cannot_get_registration_options` - ゲストは登録オプションを取得できない（401）
3. ✅ `test_guest_can_get_login_options` - ゲストがログインオプションを取得できる
4. ✅ `test_authenticated_user_can_get_their_credentials_list` - 認証済みユーザーが自分のパスキー一覧を取得できる
5. ✅ `test_user_cannot_see_other_users_credentials` - ユーザーは他のユーザーのパスキーを見られない（ユーザー分離）
6. ✅ `test_user_can_delete_their_own_credential` - ユーザーは自分のパスキーを削除できる
7. ✅ `test_user_cannot_delete_other_users_credential` - ユーザーは他のユーザーのパスキーを削除できない（404）
8. ✅ `test_guest_cannot_get_credentials_list` - ゲストはパスキー一覧を取得できない（401）
9. ✅ `test_guest_cannot_delete_credential` - ゲストはパスキーを削除できない（401）

---

## 3. 不足しているテストケース

### 🔴 重要度：高

1. ❌ **JSONレスポンス構造の検証** - 登録オプション取得時の`publicKey`構造（challenge、rp、user、pubKeyCredParamsなど）
2. ❌ **ログインオプションのJSONレスポンス構造検証** - publicKey.challenge、publicKey.rpIdなど
3. ❌ **無効なパスキーIDでの削除試行** - 存在しないIDに対する404応答
4. ❌ **複数パスキーの登録と一覧取得** - 順序確認（updated_at降順）
5. ❌ **0件のパスキー一覧取得**

### 🟠 重要度：中（ビジネスロジック）

6. ❌ **最後のパスキー削除の制限** - バックアップ認証がない場合は削除不可（403）
7. ❌ **バックアップ認証がある場合の最後のパスキー削除** - パスワードありなら削除可能
8. ❌ **バックアップ認証がある場合の最後のパスキー削除** - Google OAuthありなら削除可能
9. ❌ **複数パスキーがある場合の削除** - 常に削除可能

---

## 4. バックアップ認証とは

**バックアップ認証** = パスキー以外の認証方法

このプロジェクトでは以下の2つ：
1. **パスワード認証**（`users.password`カラム）
2. **Google OAuth認証**（`users.google_id`カラム）

### 重要性

ユーザーが最後の1つのパスキーを削除しようとした時：
- ❌ バックアップ認証なし → アカウントにログインできなくなる（ロックアウト）
- ✅ バックアップ認証あり → パスワードまたはGoogleでログイン可能

**アカウントロックアウトを防ぐための安全装置**

---

## 5. 実装する改善項目

### 改善1: ヘルパーメソッドでコード重複を解消

**問題**: 各テストで20行以上のWebAuthnCredential作成コードが重複

**解決策**: プライベートヘルパーメソッドを追加

```php
private function createCredential(User $user, array $attributes = []): WebAuthnCredential
{
    return WebAuthnCredential::unguarded(function () use ($user, $attributes) {
        return WebAuthnCredential::create(array_merge([
            'id' => 'test-cred-'.uniqid(),
            'authenticatable_type' => User::class,
            'authenticatable_id' => $user->id,
            'user_id' => $user->id,
            'alias' => 'Test Device',
            'counter' => 0,
            'rp_id' => config('webauthn.relying_party.id', 'localhost'),
            'origin' => config('app.url', 'http://localhost'),
            'transports' => json_encode(['internal']),
            'aaguid' => fake()->uuid(),
            'public_key' => base64_encode(random_bytes(65)),
            'attestation_format' => 'none',
        ], $attributes));
    });
}
```

**メリット**:
- テストコードが20行→3-5行に
- メンテナンス性向上
- カラム変更時の修正箇所が1箇所のみ

---

### 改善2: JSONレスポンス構造の詳細検証

**問題**: 現在は`assertNotEmpty($response->json())`のみで構造を検証していない

**解決策**: WebAuthn仕様に準拠した構造を`assertJsonStructure()`で検証

#### 登録オプション

```php
public function test_authenticated_user_can_get_registration_options(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/webauthn/register/options');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'challenge',
            'rp' => ['name', 'id'],
            'user' => ['id', 'name', 'displayName'],
            'pubKeyCredParams' => [
                '*' => ['type', 'alg']
            ],
            'timeout',
            'attestation',
            'authenticatorSelection' => [
                'authenticatorAttachment',
                'requireResidentKey',
                'userVerification',
            ],
        ]);
}
```

#### ログインオプション

```php
public function test_guest_can_get_login_options(): void
{
    $response = $this->postJson('/webauthn/login/options');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'challenge',
            'rpId',
            'timeout',
            'userVerification',
        ]);
}
```

---

### 改善3: 最後のパスキー削除制限の実装

#### Backend: WebAuthnController::destroy()

```php
public function destroy(Request $request, string $id): JsonResponse
{
    $user = $request->user();

    $credential = $user->webAuthnCredentials()
        ->findOrFail($id);

    // 最後のパスキーかどうかをチェック
    $remainingCredentialsCount = $user->webAuthnCredentials()->count();

    if ($remainingCredentialsCount === 1) {
        // バックアップ認証方法があるかチェック
        $hasPassword = !is_null($user->password);
        $hasGoogleOAuth = !is_null($user->google_id);

        if (!$hasPassword && !$hasGoogleOAuth) {
            return response()->json([
                'message' => '最後のパスキーは削除できません。他の認証方法を設定してください。',
            ], 403);
        }
    }

    $credential->delete();

    return response()->json([
        'message' => 'パスキーを削除しました',
    ]);
}
```

#### Test: 4つの新規テストケース

```php
// 1. バックアップ認証なし → 403
public function test_user_cannot_delete_last_credential_without_backup_auth(): void
{
    $user = User::factory()->create([
        'password' => null,
        'google_id' => null,
    ]);

    $credential = $this->createCredential($user);

    $response = $this->actingAs($user)
        ->deleteJson('/webauthn/credentials/'.urlencode($credential->id));

    $response->assertStatus(403)
        ->assertJson([
            'message' => '最後のパスキーは削除できません。他の認証方法を設定してください。',
        ]);

    $this->assertDatabaseHas('webauthn_credentials', [
        'id' => $credential->id,
    ]);
}

// 2. パスワードあり → 200
public function test_user_can_delete_last_credential_with_password(): void
{
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $credential = $this->createCredential($user);

    $response = $this->actingAs($user)
        ->deleteJson('/webauthn/credentials/'.urlencode($credential->id));

    $response->assertStatus(200);

    $this->assertDatabaseMissing('webauthn_credentials', [
        'id' => $credential->id,
    ]);
}

// 3. Google OAuthあり → 200
public function test_user_can_delete_last_credential_with_google_oauth(): void
{
    $user = User::factory()->create([
        'password' => null,
        'google_id' => 'google-id-12345',
    ]);

    $credential = $this->createCredential($user);

    $response = $this->actingAs($user)
        ->deleteJson('/webauthn/credentials/'.urlencode($credential->id));

    $response->assertStatus(200);

    $this->assertDatabaseMissing('webauthn_credentials', [
        'id' => $credential->id,
    ]);
}

// 4. 複数パスキーあり → 200
public function test_user_can_delete_non_last_credential(): void
{
    $user = User::factory()->create([
        'password' => null,
        'google_id' => null,
    ]);

    $credential1 = $this->createCredential($user, ['alias' => 'Device 1']);
    $credential2 = $this->createCredential($user, ['alias' => 'Device 2']);

    $response = $this->actingAs($user)
        ->deleteJson('/webauthn/credentials/'.urlencode($credential1->id));

    $response->assertStatus(200);

    $this->assertDatabaseMissing('webauthn_credentials', [
        'id' => $credential1->id,
    ]);
    $this->assertDatabaseHas('webauthn_credentials', [
        'id' => $credential2->id,
    ]);
}
```

---

### 改善4: エッジケース・エラーハンドリングテスト

```php
// 存在しないID削除で404
public function test_cannot_delete_nonexistent_credential(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->deleteJson('/webauthn/credentials/'.urlencode('nonexistent-id'));

    $response->assertStatus(404);
}

// 0件のパスキー一覧取得
public function test_returns_empty_array_when_user_has_no_credentials(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson('/webauthn/credentials');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
}

// パスキー一覧の並び順確認（updated_at降順）
public function test_credentials_list_is_ordered_by_updated_at_desc(): void
{
    $user = User::factory()->create();

    $credential1 = $this->createCredential($user, ['alias' => 'Old Device']);
    sleep(1); // 時間差を確実にする
    $credential2 = $this->createCredential($user, ['alias' => 'New Device']);

    $response = $this->actingAs($user)
        ->getJson('/webauthn/credentials');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.name', 'New Device')
        ->assertJsonPath('data.1.name', 'Old Device');
}
```

---

## 6. 実装後の期待結果

- **テスト総数**: 9 → 16テスト（+7テスト）
- **総合評価**: 6.5/10 → 8.5/10
- **全テストがパス**

### スコア内訳

| カテゴリ | 現在 | 改善後 | 変化 |
|---------|------|-------|------|
| 網羅性 | 6/10 | 8/10 | +2 |
| 堅牢性 | 7/10 | 9/10 | +2 |
| 標準準拠 | 7/10 | 8/10 | +1 |
| Factory使用 | 6/10 | 8/10 | +2 |
| **総合** | **6.5/10** | **8.5/10** | **+2** |

---

## 7. 変更ファイル

1. `tests/Feature/Auth/WebAuthnTest.php`
   - ヘルパーメソッド追加
   - 既存テスト簡潔化
   - 新規テスト追加（7テスト）

2. `app/Http/Controllers/Auth/WebAuthnController.php`
   - `destroy()`メソッドにビジネスロジック追加

---

## 8. Factory使用についての結論

### Laragear/WebAuthnはFactoryをサポートしていない

- `vendor/laragear/webauthn`内にFactoryファイルは存在しない
- `WebAuthnCredential`モデルに`newFactory()`メソッドは定義されていない
- plan.mdの理想的なテスト例では`WebAuthnCredential::factory()`を使用しているが、これは現時点で利用不可能

### 現在の実装方法（unguarded）が適切

**良い点**:
- `WebAuthnCredential::unguarded()`はEloquentの標準機能を使用
- Mass Assignment保護を一時的に解除
- 外部パッケージのモデルなので妥当

**改善方法**:
- ヘルパーメソッドでコード重複を解消
- 設定値を`config()`から取得してメンテナンス性向上

---

## 9. 参考資料

- `docs/feature/passkey/plan.md` - Phase 6実装計画
- `tests/Feature/Auth/AuthenticationTest.php` - 既存認証テストのパターン
- Laravel公式ドキュメント: Testing
- WebAuthn仕様: https://webauthn.guide/

---

**作成者**: Claude Code
**最終更新**: 2025-10-29
