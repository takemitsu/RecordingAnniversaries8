import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { useForm, usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';
import { type FormEventHandler, useRef, useState } from 'react';

export default function DeletePasswordForm({ className = '' }: { className?: string }) {
    const user = usePage<PageProps>().props.auth.user;
    const [confirmingPasswordDeletion, setConfirmingPasswordDeletion] = useState(false);
    const passwordInput = useRef<HTMLInputElement>(null);

    const {
        data,
        setData,
        delete: destroy,
        processing,
        reset,
        errors,
    } = useForm({
        current_password: '',
    });

    const confirmPasswordDeletion = () => {
        setConfirmingPasswordDeletion(true);
    };

    const deletePassword: FormEventHandler = (e) => {
        e.preventDefault();

        destroy(route('password.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current?.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        setConfirmingPasswordDeletion(false);

        reset();
    };

    // Google OAuthまたはパスキーがない場合は削除不可
    const hasBackupAuth = user.google_id || false; // パスキーの有無はバックエンドでチェック

    return (
        <section className={`space-y-6 ${className}`}>
            <header>
                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                    パスワードレス認証に移行
                </h2>

                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    パスワードを削除して、Google認証またはパスキーのみでログインできるようにします。
                    <br />
                    削除するには、Google認証またはパスキーが設定されている必要があります。
                </p>
            </header>

            <DangerButton onClick={confirmPasswordDeletion}>パスワードを削除</DangerButton>

            <Modal show={confirmingPasswordDeletion} onClose={closeModal}>
                <form onSubmit={deletePassword} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                        本当にパスワードを削除しますか？
                    </h2>

                    <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        パスワードを削除すると、このアカウントではパスワードログインができなくなります。
                        Google認証またはパスキーでのみログイン可能になります。
                    </p>

                    <div className="mt-6">
                        <InputLabel htmlFor="current_password" value="現在のパスワード" className="sr-only" />

                        <TextInput
                            id="current_password"
                            type="password"
                            name="current_password"
                            ref={passwordInput}
                            value={data.current_password}
                            onChange={(e) => setData('current_password', e.target.value)}
                            className="mt-1 block w-3/4"
                            isFocused
                            placeholder="現在のパスワード"
                        />

                        <InputError message={errors.current_password} className="mt-2" />
                    </div>

                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={closeModal}>キャンセル</SecondaryButton>

                        <DangerButton className="ms-3" disabled={processing}>
                            パスワードを削除
                        </DangerButton>
                    </div>
                </form>
            </Modal>
        </section>
    );
}
