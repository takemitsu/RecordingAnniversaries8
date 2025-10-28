# デプロイ手順

## 本番環境へのデプロイ

プロジェクトディレクトリ: `/var/www/ra8/`

### 基本的なデプロイ手順

```bash
cd /var/www/ra8/
git pull
npm run build
php artisan optimize
php artisan config:cache
php artisan route:cache
```

### 手順の詳細

1. **コードの取得**
   ```bash
   git pull
   ```

2. **フロントエンドビルド** (tsx/tsファイル変更時は必須)
   ```bash
   npm run build
   ```
   - TypeScriptの型チェック
   - クライアントサイドビルド
   - SSRビルド

3. **Laravelの最適化とキャッシュ**
   ```bash
   php artisan optimize
   php artisan config:cache
   php artisan route:cache
   ```

### トラブルシューティング

キャッシュ関連の問題が発生した場合:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### ログ確認

```bash
tail -f storage/logs/laravel.log
```
