import { Link } from '@inertiajs/react';
import { BUTTON_CLASSES } from '@/constants/styles';

interface BackLinkProps {
    href: string;
    children: React.ReactNode;
}

export default function BackLink({ href, children }: BackLinkProps) {
    return (
        <Link href={href} className={BUTTON_CLASSES.link}>
            {children}
        </Link>
    );
}
