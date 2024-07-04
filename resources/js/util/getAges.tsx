import dayjs from "dayjs";

export default function getAges(value: string | null): string {
    if ((!value) || value.length != 10) {
        return ''
    }
    let dt = dayjs(value, 'YYYY-MM-DD')
    if (!dt.isValid()) {
        return ''
    }
    // 未来日なら表示しない
    if (dayjs().diff(value, 'days') < 0) {
        return ''
    }

    let diff_year = dayjs().diff(value, 'years')
    return diff_year + '年（' + (diff_year + 1) + '年目）'
}
