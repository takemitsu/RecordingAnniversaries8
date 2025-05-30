import dayjs from "dayjs";
import ja from "dayjs/locale/ja";

dayjs.locale(ja);

function isValidDateString(value: string | null): boolean {
    if (!value || value.length !== 10) return false;
    return dayjs(value, 'YYYY-MM-DD').isValid();
}

type Era = {
    readonly at: string;
    readonly gengo: string;
}

const JAPANESE_ERAS: readonly Era[] = [
    {at: '2019-05-01', gengo: '令和'},
    {at: '1989-01-08', gengo: '平成'},
    {at: '1926-12-25', gengo: '昭和'},
    {at: '1912-07-30', gengo: '大正'},
    {at: '1868-01-25', gengo: '明治'},
] as const;

function calculateJapaneseYear(date: dayjs.Dayjs, eraDate: string): string {
    const yearDiff = date.year() - dayjs(eraDate).year() + 1;
    return yearDiff === 1 ? '元' : yearDiff.toString();
}

function japanDate(value: string, is_only_wa: boolean = false): string {
    if (!isValidDateString(value)) {
        return '';
    }

    let dt = dayjs(value, 'YYYY-MM-DD')
    const gengo = JAPANESE_ERAS.find(era => dt.diff(era.at, 'days', true) >= 0);
    if (!gengo) return '';

    const year = calculateJapaneseYear(dt, gengo.at);

    if (is_only_wa) {
        return `${gengo.gengo}${year}年`;
    }

    return `${gengo.gengo}${year}年${dt.month() + 1}月${dt.date()}日`;
}


function getAges(value: string | null): string {
    if (!isValidDateString(value)) {
        return '';
    }

    let dt = dayjs(value, 'YYYY-MM-DD')

    // 未来日なら表示しない
    if (dayjs().diff(value, 'days') < 0) {
        return ''
    }

    let diff_year = dayjs().diff(value, 'years')
    return `${diff_year}年（${diff_year + 1}年目）`;
}

function getTodayForHeader(): string {
    const now = dayjs().locale(ja);
    const dateTimeFormat = now.format("YYYY-MM-DD (dd) HH:mm");
    const waFormat = japanDate(now.format("YYYY-MM-DD"), true);

    return `${dateTimeFormat}（${waFormat}）`;
}

export {japanDate, getAges, getTodayForHeader}
