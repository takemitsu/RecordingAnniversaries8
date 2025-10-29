/**
 * WebAuthn エラーメッセージのユーティリティ
 */

export function getWebAuthnErrorMessage(error: Error): string {
    const errorName = error.name;
    const errorMessage = error.message;

    // ブラウザ非対応
    if (errorMessage.includes('not supported') || errorMessage.includes('undefined')) {
        return 'お使いのブラウザはパスキーに対応していません。最新のChrome、Edge、Safari、Firefoxをお使いください。';
    }

    // ユーザーキャンセル
    if (errorName === 'NotAllowedError') {
        return '認証がキャンセルされました。';
    }

    // タイムアウト
    if (errorName === 'TimeoutError') {
        return '認証がタイムアウトしました。もう一度お試しください。';
    }

    // セキュリティエラー
    if (errorName === 'SecurityError') {
        return 'セキュリティエラーが発生しました。HTTPSでアクセスしているか確認してください。';
    }

    // ネットワークエラー
    if (errorName === 'NetworkError' || errorMessage.includes('Network')) {
        return 'ネットワークエラーが発生しました。インターネット接続を確認してください。';
    }

    // すでに登録済み
    if (errorName === 'InvalidStateError') {
        return 'このデバイスは既に登録されています。';
    }

    // その他のエラー
    return `エラーが発生しました: ${errorMessage}`;
}
