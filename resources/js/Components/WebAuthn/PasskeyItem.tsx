import axios from 'axios';
import { useState } from 'react';
import { toast } from 'sonner';
import DangerButton from '@/Components/DangerButton';

interface PasskeyItemProps {
    passkey: {
        id: string;
        name: string;
        transports: string[];
        created_at: string;
        last_used_at: string;
    };
    onDelete: () => void;
}

export default function PasskeyItem({ passkey, onDelete }: PasskeyItemProps) {
    const [deleting, setDeleting] = useState(false);

    const handleDelete = async () => {
        if (!confirm('このパスキーを削除しますか？削除すると、このデバイスではパスキーログインができなくなります。')) {
            return;
        }

        try {
            setDeleting(true);
            await axios.delete(`/webauthn/credentials/${passkey.id}`);
            toast.success('パスキーを削除しました');
            onDelete();
        } catch (error) {
            console.error('パスキー削除エラー:', error);
            toast.error('パスキーの削除に失敗しました');
        } finally {
            setDeleting(false);
        }
    };

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('ja-JP', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    return (
        <div className="flex items-center justify-between p-4 border border-gray-300 dark:border-gray-600 rounded-lg">
            <div className="flex-1">
                <h3 className="font-medium text-gray-900 dark:text-gray-100">{passkey.name}</h3>
                <div className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    <p>登録日: {formatDate(passkey.created_at)}</p>
                    <p>最終使用: {formatDate(passkey.last_used_at)}</p>
                </div>
            </div>
            <div className="ml-4">
                <DangerButton onClick={handleDelete} disabled={deleting}>
                    {deleting ? '削除中...' : '削除'}
                </DangerButton>
            </div>
        </div>
    );
}
