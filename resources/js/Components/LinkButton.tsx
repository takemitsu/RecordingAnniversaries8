import { Link } from '@inertiajs/react';

interface LinkButtonProps {
    href: string;
    variant?: 'warning' | 'primary';
    children: React.ReactNode;
    className?: string;
}

export default function LinkButton({ href, variant = 'primary', children, className = '' }: LinkButtonProps) {
    const baseClasses = 'text-sm px-2 py-1 rounded-md ml-2 hover:opacity-80 transition-opacity duration-150';

    const variantClasses = {
        warning: 'text-yellow-400 dark:text-yellow-400 dark:bg-neutral-800',
        primary: 'text-sky-400 dark:text-sky-400 dark:bg-neutral-800',
    };

    return (
        <Link href={href} method="get" as="button" className={`${baseClasses} ${variantClasses[variant]} ${className}`}>
            {children}
        </Link>
    );
}
