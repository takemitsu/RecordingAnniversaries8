import PasskeyList from '@/Components/WebAuthn/PasskeyList';
import RegisterPasskey from '@/Components/WebAuthn/RegisterPasskey';

export default function RegisterPasskeyForm({ className = '' }: { className?: string }) {
    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">パスキー認証</h2>

                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    生体認証やセキュリティキーでログインできます。 パスワード不要で、より安全で便利にログインできます。
                </p>
            </header>

            <div className="mt-6 space-y-6">
                <div>
                    <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">新しいパスキーを登録</h3>
                    <RegisterPasskey />
                </div>

                <div className="pt-6 border-t border-gray-200 dark:border-gray-700">
                    <PasskeyList />
                </div>
            </div>
        </section>
    );
}
