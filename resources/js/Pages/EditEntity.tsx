import {Head, Link, useForm, usePage} from "@inertiajs/react";
import {PageProps} from "@/types";
import {FormEventHandler} from "react";
import {Entity} from "@/Pages/Entities";
import Authenticated from "@/Layouts/AuthenticatedLayout";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";
import {Textarea, Transition} from "@headlessui/react";


export default function EditEntity({entityData}: { entityData: Entity | null }) {
    const user = usePage<PageProps>().props.auth.user;

    const {data, setData, post, patch, errors, processing, recentlySuccessful} = useForm({
        id: entityData?.id,
        name: entityData?.name || '',
        desc: entityData?.desc || '',
        status: entityData?.status || 1,
    })

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (entityData?.id) {
            patch(route('entities.update', [entityData.id]));
        } else {
            post(route('entities.store'));
        }
    }

    return (
        <Authenticated
            user={user}
            header={<h2
                className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">グループ{entityData ? '編集' : '追加'}</h2>}
        >
            <Head title={`グループ${entityData ? '編集' : '追加'}`}></Head>

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">

                        <section className="max-w-xl">
                            <form onSubmit={submit} className="mt-6 space-y-6">
                                <div>
                                    <InputLabel htmlFor="name" value="グループ名"/>

                                    <TextInput
                                        id="name"
                                        className="mt-1 block w-full"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                        isFocused
                                        autoComplete="グループ名"
                                    />

                                    <InputError className="mt-2" message={errors.name}/>
                                </div>

                                <div>
                                    <InputLabel htmlFor="description" value="説明とか"/>

                                    <Textarea
                                        id="description"
                                        className="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        value={data.desc}
                                        onChange={(e) => setData('desc', e.target.value)}
                                        rows={4}
                                        autoComplete="説明とか"
                                    />

                                    <InputError className="mt-2" message={errors.desc}/>
                                </div>


                                <div className="flex items-center gap-4">
                                    <PrimaryButton disabled={processing}>保存</PrimaryButton>
                                    <Link
                                        href={route('entities.index')}
                                        className="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                                    >
                                        戻る
                                    </Link>

                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-gray-600 dark:text-gray-400">保存しました。</p>
                                    </Transition>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </div>

        </Authenticated>
    )
}
