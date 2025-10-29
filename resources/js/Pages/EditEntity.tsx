import { Head, useForm } from '@inertiajs/react';
import type { FormEventHandler } from 'react';
import BackLink from '@/Components/BackLink';
import FormContainer from '@/Components/FormContainer';
import FormField from '@/Components/FormField';
import FormSuccessMessage from '@/Components/FormSuccessMessage';
import PrimaryButton from '@/Components/PrimaryButton';
import { useAuthUser } from '@/hooks/useAuthUser';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import type { Entity } from '@/types';

export default function EditEntity({ entityData }: { entityData: Entity | null }) {
    const user = useAuthUser();

    const { data, setData, post, patch, errors, processing, recentlySuccessful } = useForm({
        id: entityData?.id,
        name: entityData?.name || '',
        desc: entityData?.desc || '',
        status: entityData?.status || 1,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (entityData?.id) {
            patch(route('entities.update', [entityData.id]));
        } else {
            post(route('entities.store'));
        }
    };

    return (
        <Authenticated
            user={user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    グループ{entityData ? '編集' : '追加'}
                </h2>
            }
        >
            <Head title={`グループ${entityData ? '編集' : '追加'}`}></Head>

            <FormContainer>
                <form onSubmit={submit} className="mt-6 space-y-6">
                    <FormField
                        id="name"
                        label="グループ名"
                        error={errors.name}
                        inputProps={{
                            value: data.name,
                            onChange: (e) => setData('name', e.target.value),
                            required: true,
                            autoFocus: true,
                            autoComplete: 'グループ名',
                        }}
                    />

                    <FormField
                        id="description"
                        label="説明とか"
                        type="textarea"
                        error={errors.desc}
                        inputProps={{
                            value: data.desc,
                            onChange: (e) => setData('desc', e.target.value),
                            rows: 4,
                            autoComplete: '説明とか',
                        }}
                    />

                    <div className="flex items-center gap-4">
                        <PrimaryButton disabled={processing}>保存</PrimaryButton>
                        <BackLink href={route('entities.index')}>戻る</BackLink>

                        <FormSuccessMessage show={recentlySuccessful} />
                    </div>
                </form>
            </FormContainer>
        </Authenticated>
    );
}
