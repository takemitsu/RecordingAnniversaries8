import {Head} from '@inertiajs/react';
import dayjs from "dayjs";
import ja from "dayjs/locale/ja";
import {japanDate} from "@/util/japanDate";
import SecondaryButton from "@/Components/SecondaryButton";

dayjs.locale(ja);

export default function Years() {
    const range = (start: number, end: number) => [...Array(end - start + 1).keys()].map((elem) => elem + start)
    const years = range(1900, dayjs().year()).reverse()

    const startDayOfNendo = (year: number, format?: string) => {
        return dayjs(year.toString() + '-04-01').format(format ?? 'YYYY-MM-DD');
    }
    const endDayOfNendo = (year: number, format?: string) => {
        year++;
        return dayjs(year.toString() + '-03-31').format(format ?? 'YYYY-MM-DD');
    }

    const endOfYear = (year: number, format?: string) => {
        return dayjs().set('year', year).endOf('year').format(format ?? 'YYYY-MM-DD');
    }

    return (
        <div
            className="min-h-screen flex flex-col sm:justify-center items-center pt-0 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div className="w-full sm:max-w-md mt-3 ml-5 sm:rounded-lg">
                <SecondaryButton type="button" onClick={() => {
                    location.href = "/"
                }}>
                    戻る
                </SecondaryButton>
            </div>
            <div
                className="w-full sm:max-w-md mt-3 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                <Head title="西暦/和暦一覧"/>
                <main>
                    <div className="flex items-center justify-start gap-6">
                        <div>
                            <h2 className="text-xl font-semibold text-black dark:text-white">
                                年度一覧
                            </h2>

                            <p className="mt-2 text-sm/relaxed text-gray-400 dark:text-gray-400">
                                今日は{japanDate(dayjs().format("YYYY-MM-DD"), true)}、{dayjs().format('YYYY年MM月DD日（ddd）')}です。<br/>
                                年度は12/31基準で元年が表示されるようにしています。
                            </p>
                        </div>
                    </div>

                    <div className=" bg-white dark:bg-gray-800">
                        <table className="border-collapse border border-slate-500">
                            <thead>
                            <tr>
                                <th className="border border-slate-600 text-gray-600 dark:text-gray-200">No.</th>
                                <th className="border border-slate-600 text-gray-600 dark:text-gray-200">年度</th>
                                <th className="border border-slate-600 text-gray-600 dark:text-gray-200">開始</th>
                                <th className="border border-slate-600 text-gray-600 dark:text-gray-200">終了</th>
                            </tr>
                            </thead>
                            <tbody>
                            {years.map((year: number, i: number) => (
                                <tr key={'ty' + year}>
                                    <td className="border border-slate-600 px-2 py-1 text-gray-400 dark:text-gray-400 text-right">{i}</td>
                                    <td className="border border-slate-600 px-2 py-1 text-gray-400 dark:text-gray-400">{japanDate(endOfYear(year), true)}度</td>
                                    <td className="border border-slate-600 px-2 py-1 text-gray-400 dark:text-gray-400">{startDayOfNendo(year, "YYYY年M月")}</td>
                                    <td className="border border-slate-600 px-2 py-1 text-gray-400 dark:text-gray-400">{endDayOfNendo(year, "YYYY年M月")}</td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>
                </main>
            </div>
        </div>
    );
}
