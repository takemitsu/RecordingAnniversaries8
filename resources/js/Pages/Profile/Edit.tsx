import { Head, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import CreatePasswordForm from '@/Pages/Profile/Partials/CreatePasswordForm';
import DeletePasswordForm from '@/Pages/Profile/Partials/DeletePasswordForm';
import RegisterPasskeyForm from '@/Pages/Profile/Partials/RegisterPasskeyForm';
import UpdateGoogleAuth from '@/Pages/Profile/Partials/UpdateGoogleAuth';
import type { PageProps } from '@/types';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({
    auth,
    mustVerifyEmail,
    status,
    hasPassword,
}: PageProps<{
    mustVerifyEmail: boolean;
    status?: string;
    hasPassword: boolean;
}>) {
    const user = usePage<PageProps>().props.auth.user;

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Profile</h2>}
        >
            <Head title="Profile" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <UpdateProfileInformationForm
                            mustVerifyEmail={mustVerifyEmail}
                            status={status}
                            className="max-w-xl"
                        />
                    </div>

                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div className="space-y-6">
                            {hasPassword ? (
                                <UpdatePasswordForm className="max-w-xl" />
                            ) : (
                                <CreatePasswordForm className="max-w-xl" />
                            )}

                            {hasPassword && (
                                <>
                                    <hr className="border-gray-200 dark:border-gray-700" />
                                    <DeletePasswordForm className="max-w-xl" />
                                </>
                            )}
                        </div>
                    </div>

                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <UpdateGoogleAuth className="max-w-xl" />
                    </div>

                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <RegisterPasskeyForm className="max-w-xl" />
                    </div>

                    {!user.google_id && (
                        <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                            <DeleteUserForm className="max-w-xl" />
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
