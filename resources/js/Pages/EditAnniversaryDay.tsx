import {Day, Entity} from "@/Pages/Entities";
import {Head, Link, useForm, usePage} from "@inertiajs/react";
import {PageProps} from "@/types";
import {FormEventHandler, useState} from "react";
import Authenticated from "@/Layouts/AuthenticatedLayout";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import InputError from "@/Components/InputError";
import {Textarea, Transition} from "@headlessui/react";
import PrimaryButton from "@/Components/PrimaryButton";
import DatePicker, {registerLocale} from 'react-datepicker'
import dayjs from "dayjs";
import {japanDate} from "@/util/japanDate";
import {ja} from 'date-fns/locale/ja';

registerLocale('ja', ja)

export default function EditAnniversaryDay({entityData, dayData, status}: {
    entityData: Entity,
    dayData: Day,
    status: any
}) {
    const user = usePage<PageProps>().props.auth.user;
    const {data, setData, post, patch, errors, processing, recentlySuccessful} = useForm({
        id: dayData?.id,
        name: dayData?.name ?? "",
        desc: dayData?.desc ?? "",
        anniv_at: dayData?.anniv_at ?? dayjs().toDate(),
    })

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (entityData?.id && dayData?.id) {
            patch(route('entities.days.update', [entityData.id, dayData.id]));
        } else {
            post(route('entities.days.store', [entityData.id]));
        }
    }

    const setTimeZero = (date: string): Date => {
        return dayjs(date).hour(0).minute(0).second(0).millisecond(0).toDate()
    }

    const [selectDate, setSelectDate] = useState(dayData ? setTimeZero(dayData.anniv_at) : setTimeZero(dayjs().toISOString()))
    const [reki, setReki] = useState(dayData ? japanDate(dayData.anniv_at, true) : japanDate(dayjs().format('YYYY-MM-DD'), true))

    const onHandleChangeAnnivAt = (date: Date | null): void => {
        if (date !== null) {
            setSelectDate(date)
            setData('anniv_at', dayjs(date).format('YYYY-MM-DD'))
            setReki(japanDate(dayjs(date).format('YYYY-MM-DD'), true))
        }
    }
    const onHandleSelectAnnivAt = (date: Date | null): void => {
        onHandleChangeAnnivAt(date)
    }

    return (
        <Authenticated
            user={user}
            header={<h2
                className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">記念日{dayData ? '編集' : '追加'}</h2>}
        >
            <Head title={`記念日${dayData ? '編集' : '追加'}`}></Head>

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">

                        <section className="max-w-xl">
                            <form onSubmit={submit} className="mt-6 space-y-6">
                                <div>
                                    <InputLabel htmlFor="name" value="記念日名"/>

                                    <TextInput
                                        id="name"
                                        className="mt-1 block w-full"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                        isFocused
                                        autoComplete="記念日名"
                                    />

                                    <InputError className="mt-2" message={errors.name}/>
                                </div>

                                <div>
                                    <InputLabel htmlFor="anniversary" value="記念日"/>

                                    <div className="flex items-center">
                                        <DatePicker
                                            showIcon
                                            selected={selectDate}
                                            onChange={onHandleChangeAnnivAt}
                                            onSelect={onHandleSelectAnnivAt}
                                            locale="ja"
                                            popperClassName="d-none"
                                            dateFormat="yyyy-MM-dd"
                                            className="rounded"
                                            open={false}
                                        />
                                        <div className="flex-wrap flex-shrink-0 ml-2 text-gray-600 dark:text-gray-200">
                                            {reki}
                                        </div>
                                    </div>
                                    <DatePicker
                                        selected={selectDate}
                                        onChange={onHandleChangeAnnivAt}
                                        onSelect={onHandleSelectAnnivAt}
                                        locale="ja"
                                        inline
                                        dateFormat="yyyy-MM-dd"
                                        showMonthDropdown
                                        showYearDropdown
                                        dropdownMode="select"
                                        todayButton="今日に移動"
                                    />

                                    <InputError message={errors.anniv_at}/>
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
