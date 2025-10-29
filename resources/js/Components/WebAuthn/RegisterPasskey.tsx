import { startRegistration } from '@simplewebauthn/browser';
import axios from 'axios';
import { useState } from 'react';
import { toast } from 'sonner';
import PrimaryButton from '@/Components/PrimaryButton';
import { getWebAuthnErrorMessage } from '@/util/webauthn-errors';

export default function RegisterPasskey() {
    const [loading, setLoading] = useState(false);

    const handleRegister = async () => {
        try {
            setLoading(true);

            // サーバーからオプションを取得
            const { data: options } = await axios.post('/webauthn/register/options');

            // ブラウザのWebAuthn APIを呼び出し
            // @simplewebauthn/browser v11以降は { optionsJSON } 形式で渡す
            const credential = await startRegistration({ optionsJSON: options });

            // サーバーに登録
            await axios.post('/webauthn/register', credential);

            toast.success('パスキーが登録されました！');

            // ページをリロードして更新を反映
            window.location.reload();
        } catch (error) {
            console.error('パスキー登録エラー:', error);
            const message = getWebAuthnErrorMessage(error as Error);
            toast.error(message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <PrimaryButton onClick={handleRegister} disabled={loading}>
            {loading ? '登録中...' : 'パスキーを登録'}
        </PrimaryButton>
    );
}
