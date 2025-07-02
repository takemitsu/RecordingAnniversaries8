import {Day, Entity} from "@/types";
import {Head, useForm} from "@inertiajs/react";
import {FormEventHandler, useState} from "react";
import Authenticated from "@/Layouts/AuthenticatedLayout";
import {useAuthUser} from "@/hooks/useAuthUser";
import InputLabel from "@/Components/InputLabel";
import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";
import FormContainer from "@/Components/FormContainer";
import FormSuccessMessage from "@/Components/FormSuccessMessage";
import BackLink from "@/Components/BackLink";
import FormField from "@/Components/FormField";
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
    const user = useAuthUser();
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

            <FormContainer>
                <form onSubmit={submit} className="mt-6 space-y-6">
                                <FormField
                                    id="name"
                                    label="記念日名"
                                    error={errors.name}
                                    inputProps={{
                                        value: data.name,
                                        onChange: (e) => setData('name', e.target.value),
                                        required: true,
                                        autoFocus: true,
                                        autoComplete: "記念日名"
                                    }}
                                />

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

                                <FormField
                                    id="description"
                                    label="説明とか"
                                    type="textarea"
                                    error={errors.desc}
                                    inputProps={{
                                        value: data.desc,
                                        onChange: (e) => setData('desc', e.target.value),
                                        rows: 4,
                                        autoComplete: "説明とか"
                                    }}
                                />

                                <div className="flex items-center gap-4">
                                    <PrimaryButton disabled={processing}>保存</PrimaryButton>
                                    <BackLink href={route('entities.index')}>
                                        戻る
                                    </BackLink>

                                    <FormSuccessMessage show={recentlySuccessful} />
                                </div>
                            </form>
            </FormContainer>

        </Authenticated>
    )
}
