import {useRef, FormEventHandler} from 'react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import {useForm, usePage} from '@inertiajs/react';
import {Transition} from '@headlessui/react';
import {PageProps} from "@/types";

export default function UpdateGoogleAuth({className = ''}: { className?: string }) {
    const user = usePage<PageProps>().props.auth.user;
    const redirectGoogle = () => {
        location.href = route('auth.redirect.google');
    }

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Register Google Auth</h2>

                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {user.google_id ? (
                        <>
                            登録済みです。
                        </>
                    ) : (
                        <>
                            google ログインを行う場合は以下のボタンを押下してください。
                        </>
                    )}

                </p>
            </header>

            {!user.google_id ? (
                <div className="mt-6 space-y-6">
                    <a type="buttn" onClick={redirectGoogle}>
                        <img src={'img/web_dark_rd_SI.svg'} alt="sign in with Google"/>
                    </a>
                </div>
            ): (
                <></>
            )}
        </section>
    );
}
