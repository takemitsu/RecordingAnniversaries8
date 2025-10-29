import axios from 'axios';
import { useEffect, useState } from 'react';
import PasskeyItem from './PasskeyItem';

interface Passkey {
    id: string;
    name: string;
    transports: string[];
    created_at: string;
    last_used_at: string;
}

export default function PasskeyList() {
    const [passkeys, setPasskeys] = useState<Passkey[]>([]);
    const [loading, setLoading] = useState(true);

    const fetchPasskeys = async () => {
        try {
            setLoading(true);
            const { data } = await axios.get<{ data: Passkey[] }>('/webauthn/credentials');
            setPasskeys(data.data);
        } catch (error) {
            console.error('パスキー一覧取得エラー:', error);
        } finally {
            setLoading(false);
        }
    };

    // biome-ignore lint/correctness/useExhaustiveDependencies: fetchPasskeysは初回のみ実行
    useEffect(() => {
        fetchPasskeys();
    }, []);

    if (loading) {
        return (
            <div className="text-center py-4">
                <p className="text-sm text-gray-600 dark:text-gray-400">読み込み中...</p>
            </div>
        );
    }

    if (passkeys.length === 0) {
        return (
            <div className="text-center py-4 px-4 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                <p className="text-sm text-gray-600 dark:text-gray-400">登録されているパスキーはありません</p>
            </div>
        );
    }

    return (
        <div className="space-y-3">
            <p className="text-sm text-gray-600 dark:text-gray-400">登録済みのパスキー ({passkeys.length}個)</p>
            {passkeys.map((passkey) => (
                <PasskeyItem key={passkey.id} passkey={passkey} onDelete={fetchPasskeys} />
            ))}
        </div>
    );
}
