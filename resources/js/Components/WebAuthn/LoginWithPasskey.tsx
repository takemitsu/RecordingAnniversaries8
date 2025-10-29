import { startAuthentication } from '@simplewebauthn/browser';
import axios from 'axios';
import { useState } from 'react';
import { toast } from 'sonner';
import PrimaryButton from '@/Components/PrimaryButton';
import { getWebAuthnErrorMessage } from '@/util/webauthn-errors';

export default function LoginWithPasskey() {
    const [loading, setLoading] = useState(false);

    const handleLogin = async () => {
        try {
            setLoading(true);

            // サーバーからオプションを取得
            const { data: options } = await axios.post('/webauthn/login/options');

            // ブラウザのWebAuthn APIを呼び出し
            // @simplewebauthn/browser v11以降は { optionsJSON } 形式で渡す
            const credential = await startAuthentication({ optionsJSON: options });

            // サーバーで認証
            await axios.post('/webauthn/login', credential);

            // ログイン成功後、フルページリロードでダッシュボードに遷移
            // （セッションクッキーを確実に使用するため）
            window.location.href = route('dashboard');
        } catch (error) {
            console.error('パスキー認証エラー:', error);
            const message = getWebAuthnErrorMessage(error as Error);
            toast.error(message);
            setLoading(false);
        }
    };

    return (
        <PrimaryButton onClick={handleLogin} disabled={loading} className="w-full justify-center">
            {loading ? '認証中...' : '🔐 パスキーでログイン'}
        </PrimaryButton>
    );
}
