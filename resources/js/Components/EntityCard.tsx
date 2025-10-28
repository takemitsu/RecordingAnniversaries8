import type { Day, Entity } from '@/types';
import AnniversaryDayCard from './AnniversaryDayCard';

interface EntityCardProps {
    entity: Entity;
    actions?: React.ReactNode;
    dayActions?: (entity: Entity, day: Day) => React.ReactNode;
}

export default function EntityCard({ entity, actions, dayActions }: EntityCardProps) {
    return (
        <div className="p-2 border-t border-gray-200 first:border-0 lg:first:border-t">
            <div className="text-gray-600 dark:text-gray-200 font-semibold text-xl">{entity.name}</div>

            <div className="text-gray-400 dark:text-gray-400 whitespace-pre-line">{entity.desc}</div>

            {actions && <div className="flex justify-end mb-2">{actions}</div>}

            {entity.days.map((day) => (
                <AnniversaryDayCard key={`D${day.id}`} day={day}>
                    {dayActions?.(entity, day)}
                </AnniversaryDayCard>
            ))}
        </div>
    );
}
