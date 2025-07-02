import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {Head, Link} from '@inertiajs/react';
import {Entity} from '@/types';
import EntityCard from '@/Components/EntityCard';
import {useAuthUser} from '@/hooks/useAuthUser';

export default function Dashboard({entities}: { entities: Entity[] }) {
    const user = useAuthUser();
    return (
        <AuthenticatedLayout
            user={user}
        >
            <Head title="一覧"/>

            <div className="lg:p-12 p-2">
                {entities.map((entity: Entity) => (
                    <EntityCard key={'E' + entity.id} entity={entity} />
                ))}
                {entities.length === 0 && (
                    <div className="m-4 p-2 bg-gray-800 text-gray-200 rounded text-center">
                        データがありません。<br/>
                        <Link
                            href={route('entities.index')}
                            method="get"
                            className="text-sky-400 dark:text-sky-400 pr-2"
                        >
                            こちら
                        </Link>
                        からデータを登録してください。
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
