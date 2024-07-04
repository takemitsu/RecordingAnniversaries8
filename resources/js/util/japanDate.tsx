import dayjs from "dayjs";

export default function japanDate(value: string, is_only_wa: boolean = false): string {
    if (!value) {
        return ''
    }
    if (value.length !== 10) {
        return ''
    }

    let dt = dayjs(value, 'YYYY-MM-DD')
    if (!dt.isValid()) {
        return ''
    }

    const dates = [
        {at: '2019-05-01', gengo: '令和'},
        {at: '1989-01-08', gengo: '平成'},
        {at: '1926-12-25', gengo: '昭和'},
        {at: '1912-07-30', gengo: '大正'},
        {at: '1868-01-25', gengo: '明治'},
    ]

    let gengo = null

    for (let n of dates) {
        if (dt.diff(n.at, 'days', true) >= 0) {
            gengo = n
            break
        }
    }

    if (gengo === null) {
        return ''
    }

    let year: number | string = dt.year() - dayjs(gengo.at).year() + 1
    if (year === 1) {
        year = '元'
    }

    if (is_only_wa) {
        return gengo.gengo + year + '年'
    }

    return gengo.gengo + year + '年' + (dt.month() + 1) + '月' + dt.date() + '日'
}
