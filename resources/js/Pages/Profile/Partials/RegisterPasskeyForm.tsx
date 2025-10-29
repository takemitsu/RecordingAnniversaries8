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
                <RegisterPasskey />
            </div>
        </section>
    );
}
