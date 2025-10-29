import { beforeAll, describe, expect, it, vi } from 'vitest';
import { getAges, getTodayForHeader, japanDate } from '../japanDate';

describe('japanDate', () => {
    describe('基本的な和暦変換', () => {
        it('令和の日付を正しく変換する', () => {
            expect(japanDate('2019-05-01')).toBe('令和元年5月1日');
            expect(japanDate('2019-05-02')).toBe('令和元年5月2日');
            expect(japanDate('2020-01-01')).toBe('令和2年1月1日');
            expect(japanDate('2024-12-31')).toBe('令和6年12月31日');
        });

        it('平成の日付を正しく変換する', () => {
            expect(japanDate('1989-01-08')).toBe('平成元年1月8日');
            expect(japanDate('1989-12-31')).toBe('平成元年12月31日');
            expect(japanDate('2019-04-30')).toBe('平成31年4月30日');
        });

        it('昭和の日付を正しく変換する', () => {
            expect(japanDate('1926-12-25')).toBe('昭和元年12月25日');
            expect(japanDate('1989-01-07')).toBe('昭和64年1月7日');
        });

        it('大正の日付を正しく変換する', () => {
            expect(japanDate('1912-07-30')).toBe('大正元年7月30日');
            expect(japanDate('1926-12-24')).toBe('大正15年12月24日');
        });

        it('明治の日付を正しく変換する', () => {
            expect(japanDate('1868-01-25')).toBe('明治元年1月25日');
            expect(japanDate('1912-07-29')).toBe('明治45年7月29日');
        });
    });

    describe('元号の境界日テスト', () => {
        it('令和と平成の境界日を正しく処理する', () => {
            expect(japanDate('2019-04-30')).toBe('平成31年4月30日');
            expect(japanDate('2019-05-01')).toBe('令和元年5月1日');
        });

        it('平成と昭和の境界日を正しく処理する', () => {
            expect(japanDate('1989-01-07')).toBe('昭和64年1月7日');
            expect(japanDate('1989-01-08')).toBe('平成元年1月8日');
        });

        it('昭和と大正の境界日を正しく処理する', () => {
            expect(japanDate('1926-12-24')).toBe('大正15年12月24日');
            expect(japanDate('1926-12-25')).toBe('昭和元年12月25日');
        });
    });

    describe('元年の表示テスト', () => {
        it('各元号の元年を正しく「元」で表示する', () => {
            expect(japanDate('2019-05-01')).toContain('令和元年');
            expect(japanDate('1989-01-08')).toContain('平成元年');
            expect(japanDate('1926-12-25')).toContain('昭和元年');
            expect(japanDate('1912-07-30')).toContain('大正元年');
            expect(japanDate('1868-01-25')).toContain('明治元年');
        });

        it('2年目以降は数字で表示する', () => {
            expect(japanDate('2020-01-01')).toContain('令和2年');
            expect(japanDate('1990-01-01')).toContain('平成2年');
            expect(japanDate('1927-01-01')).toContain('昭和2年');
        });
    });

    describe('is_only_wa オプション', () => {
        it('is_only_wa=true の場合、年号のみを返す', () => {
            expect(japanDate('2019-05-01', true)).toBe('令和元年');
            expect(japanDate('2020-12-31', true)).toBe('令和2年');
            expect(japanDate('2019-04-30', true)).toBe('平成31年');
        });

        it('is_only_wa=false の場合、完全な日付を返す', () => {
            expect(japanDate('2019-05-01', false)).toBe('令和元年5月1日');
            expect(japanDate('2020-12-31', false)).toBe('令和2年12月31日');
        });
    });

    describe('無効な日付の処理', () => {
        it('空文字列や null を適切に処理する', () => {
            expect(japanDate('')).toBe('');
            expect(japanDate('invalid-date')).toBe('');
        });

        it('不正な日付フォーマットを適切に処理する', () => {
            // dayjs は柔軟なため、スラッシュ形式も有効な日付として認識する
            expect(japanDate('2019/05/01')).toBe('令和元年5月1日'); // スラッシュ形式も有効
            expect(japanDate('20190501')).toBe(''); // ハイフンなし、8文字
            expect(japanDate('abc-de-fg')).toBe(''); // 明らかに無効
        });

        it('長さが10文字でない日付文字列を拒否する', () => {
            expect(japanDate('2019-5-1')).toBe('');
            expect(japanDate('2019-05-1')).toBe('');
            expect(japanDate('19-05-01')).toBe('');
        });

        it('明治以前の日付は空文字列を返す', () => {
            expect(japanDate('1867-12-31')).toBe('');
            expect(japanDate('1800-01-01')).toBe('');
        });
    });

    describe('年計算の正確性', () => {
        it('同一年内での年計算が正確', () => {
            // 令和元年（2019年）内でのテスト
            expect(japanDate('2019-05-01', true)).toBe('令和元年');
            expect(japanDate('2019-12-31', true)).toBe('令和元年');

            // 令和2年（2020年）内でのテスト
            expect(japanDate('2020-01-01', true)).toBe('令和2年');
            expect(japanDate('2020-12-31', true)).toBe('令和2年');
        });

        it('年跨ぎでの年計算が正確', () => {
            expect(japanDate('2019-05-01', true)).toBe('令和元年'); // 2019年
            expect(japanDate('2020-01-01', true)).toBe('令和2年'); // 2020年
            expect(japanDate('2021-01-01', true)).toBe('令和3年'); // 2021年
        });
    });
});

describe('getAges', () => {
    beforeAll(() => {
        // 現在日付を固定（2024-01-01）
        vi.useFakeTimers();
        vi.setSystemTime(new Date('2024-01-01'));
    });

    it('過去の日付から正しい年齢を計算する', () => {
        expect(getAges('2020-01-01')).toBe('4年（5年目）');
        expect(getAges('2010-01-01')).toBe('14年（15年目）');
    });

    it('未来の日付では空文字列を返す', () => {
        expect(getAges('2025-01-01')).toBe('');
        expect(getAges('2024-12-31')).toBe('');
    });

    it('無効な日付では空文字列を返す', () => {
        expect(getAges('')).toBe('');
        expect(getAges('invalid')).toBe('');
        expect(getAges(null)).toBe('');
    });

    it('今日の日付では0年を返す', () => {
        expect(getAges('2024-01-01')).toBe('0年（1年目）');
    });
});

describe('getTodayForHeader', () => {
    beforeAll(() => {
        // 現在日時を固定（令和6年1月1日）
        vi.useFakeTimers();
        vi.setSystemTime(new Date('2024-01-01 10:30:00'));
    });

    it('今日の日付を和暦付きで正しくフォーマットする', () => {
        const result = getTodayForHeader();
        expect(result).toContain('2024-01-01');
        expect(result).toContain('10:30');
        expect(result).toContain('令和6年');
    });

    it('曜日が含まれている', () => {
        const result = getTodayForHeader();
        // 2024-01-01は月曜日
        expect(result).toContain('(月)');
    });

    it('形式が正しい', () => {
        const result = getTodayForHeader();
        // "YYYY-MM-DD (dd) HH:mm（令和X年）" の形式
        expect(result).toMatch(/^\d{4}-\d{2}-\d{2} \(.+\) \d{2}:\d{2}（令和\d+年）$/);
    });
});
