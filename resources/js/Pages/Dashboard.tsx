import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {Head, Link, usePage} from '@inertiajs/react';
import {japanDate,getAges} from "@/util/japanDate";
import {PageProps} from '@/types';
import {Entity} from "@/Pages/Entities";

export default function Dashboard({entities}: { entities: Entity[] }) {
    const user = usePage<PageProps>().props.auth.user;
    return (
        <AuthenticatedLayout
            user={user}
        >
            <Head title="一覧"/>

            <div className="lg:p-12 p-2">

                {entities.map((entity: Entity) => (
                    <div key={'E' + entity.id}
                         className="p-2 border-t border-gray-200 first:border-0 lg:first:border-t">

                        <div className="text-gray-600 dark:text-gray-200 font-semibold text-xl">
                            {entity.name}
                        </div>

                        <div className="text-gray-400 dark:text-gray-400 whitespace-pre-line">
                            {entity.desc}
                        </div>

                        {entity.days.map((day) => (
                            <div key={'D' + day.id} className="p-2 pt-3 mt-2 border-t border-gray-200 dark:border-gray-600">
                                <div className="text-gray-200 text-base">
                                    <span className="text-blue-400 font-bold">{day.name}</span>
                                    <span className="text-sm mx-2">まで</span>
                                    <span className="text-pink-400 font-bold">{day.diff_days}</span>
                                    <span className="text-sm ml-2">日</span>
                                </div>
                                <div className="text-gray-200">
                                    <span>{day.anniv_at}</span>
                                    <span>（{japanDate(day.anniv_at, true)}）</span>
                                    <span>{getAges(day.anniv_at)}</span>
                                </div>
                                <div className="text-gray-400 dark:text-gray-400 whitespace-pre-line">
                                    {entity.desc}
                                </div>
                            </div>
                        ))}
                    </div>
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
