import {PageProps} from "@/types";
import Authenticated from "@/Layouts/AuthenticatedLayout";
import {Head, Link, usePage} from "@inertiajs/react";
import {getAges, japanDate} from "@/util/japanDate";
import TextButton from "@/Components/TextButton";
import {Inertia} from "@inertiajs/inertia";

export interface Day {
    id: number;
    name: string;
    desc: string;
    anniv_at: string;
    diff_days: number;
}

export interface Entity {
    id: number;
    name: string;
    desc: string;
    status: number;
    days: Day[];
}

export default function Entities({entities}: { entities: Entity[] }) {
    const user = usePage<PageProps>().props.auth.user;

    function handleRemoveEntity(entity: Entity) {
        if (confirm('remove this entity: ' + entity.name)) {
            Inertia.delete(route('entities.destroy', {entity: entity.id}))
        }
    }

    function handleRemoveDay(entity: Entity, day: Day) {
        if (confirm('remove this day: ' + day.name)) {
            Inertia.delete(route('entities.days.destroy', {entity: entity.id, day: day.id}))
        }
    }

    return (
        <Authenticated
            user={user}
        >
            <Head title="編集"/>

            <div className="lg:p-12 p-2">

                {entities.map((entity: Entity) => (
                    <div key={'E' + entity.id} className="p-2 border-t border-gray-200 first:border-0 lg:first:border-t">

                        <div className="text-gray-600 dark:text-gray-200 font-semibold text-xl">
                            {entity.name}
                        </div>

                        <div className="text-gray-400 dark:text-gray-400 whitespace-pre-line">
                            {entity.desc}
                        </div>
                        <div className="flex justify-end mb-2">
                            <TextButton
                                type="button"
                                className="bg-neutral-200 text-orange-500 dark:bg-neutral-800 dark:text-pink-400 ml-2"
                                onClick={() => handleRemoveEntity(entity)}>
                                削除
                            </TextButton>

                            <Link
                                href={route('entities.edit', {entity: entity.id})}
                                method="get"
                                as="button"
                                className="text-sm text-yellow-400 dark:text-yellow-400 px-2 py-1 rounded-md dark:bg-neutral-800 ml-2"
                            >
                                編集
                            </Link>

                            <Link
                                href={route('entities.days.create', {entity: entity.id})}
                                method="get"
                                as="button"
                                className="text-sm text-sky-400 dark:text-sky-400 px-2 py-1 rounded-md dark:bg-neutral-800 ml-2"
                            >
                                記念日追加
                            </Link>
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
                                    {day.desc}
                                </div>

                                <div className="flex justify-end mb-2">
                                    <TextButton
                                        type="button"
                                        className="bg-neutral-200 text-orange-500 dark:bg-neutral-800 dark:text-pink-400 ml-2"
                                        onClick={() => handleRemoveDay(entity, day)}>
                                        削除
                                    </TextButton>

                                    <Link
                                        href={route('entities.days.edit', {entity: entity.id, day: day.id})}
                                        method="get"
                                        as="button"
                                        className="text-sm text-yellow-400 dark:text-yellow-400 px-2 py-1 rounded-md dark:bg-neutral-800 ml-2"
                                    >
                                        編集
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>
                ))}

                <div className="flex justify-end border-t border-gray-200 first:border-0 pt-2">
                    <Link
                        href={route('entities.create')}
                        method="get"
                        as="button"
                        className="text-sm text-sky-400 dark:text-sky-400 px-2 py-1 rounded-md dark:bg-neutral-800 ml-2"
                    >
                        グループ追加
                    </Link>
                </div>

            </div>
        </Authenticated>
    )
}
