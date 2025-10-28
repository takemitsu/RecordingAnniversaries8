import { Head } from '@inertiajs/react';
import ActionButton from '@/Components/ActionButton';
import EntityCard from '@/Components/EntityCard';
import LinkButton from '@/Components/LinkButton';
import { useAuthUser } from '@/hooks/useAuthUser';
import { useConfirmDelete } from '@/hooks/useConfirmDelete';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import type { Day, Entity } from '@/types';

export default function Entities({ entities }: { entities: Entity[] }) {
    const user = useAuthUser();
    const { confirmDelete } = useConfirmDelete();

    function handleRemoveEntity(entity: Entity) {
        confirmDelete(entity.name, 'entity', route('entities.destroy', { entity: entity.id }));
    }

    function handleRemoveDay(entity: Entity, day: Day) {
        confirmDelete(day.name, 'day', route('entities.days.destroy', { entity: entity.id, day: day.id }));
    }

    return (
        <Authenticated user={user}>
            <Head title="編集" />

            <div className="lg:p-12 p-2">
                {entities.map((entity: Entity) => (
                    <EntityCard
                        key={`E${entity.id}`}
                        entity={entity}
                        actions={
                            <>
                                <ActionButton variant="danger" onClick={() => handleRemoveEntity(entity)}>
                                    削除
                                </ActionButton>

                                <LinkButton variant="warning" href={route('entities.edit', { entity: entity.id })}>
                                    編集
                                </LinkButton>

                                <LinkButton href={route('entities.days.create', { entity: entity.id })}>
                                    記念日追加
                                </LinkButton>
                            </>
                        }
                        dayActions={(entity, day) => (
                            <>
                                <ActionButton variant="danger" onClick={() => handleRemoveDay(entity, day)}>
                                    削除
                                </ActionButton>

                                <LinkButton
                                    variant="warning"
                                    href={route('entities.days.edit', { entity: entity.id, day: day.id })}
                                >
                                    編集
                                </LinkButton>
                            </>
                        )}
                    />
                ))}

                <div className="flex justify-end border-t border-gray-200 first:border-0 pt-2">
                    <LinkButton href={route('entities.create')}>グループ追加</LinkButton>
                </div>
            </div>
        </Authenticated>
    );
}
