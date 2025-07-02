import { Day } from '@/types';
import { japanDate, getAges } from '@/util/japanDate';

interface AnniversaryDayCardProps {
    day: Day;
    children?: React.ReactNode;
}

export default function AnniversaryDayCard({ day, children }: AnniversaryDayCardProps) {
    return (
        <div className="p-2 pt-3 mt-2 border-t border-gray-200 dark:border-gray-600">
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
            {children && (
                <div className="flex justify-end mb-2">
                    {children}
                </div>
            )}
        </div>
    );
}