import type { ButtonHTMLAttributes } from 'react';

interface ActionButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: 'danger' | 'warning' | 'primary';
}

export default function ActionButton({ variant = 'primary', className = '', children, ...props }: ActionButtonProps) {
    const baseClasses = 'text-sm px-2 py-1 rounded-md ml-2 hover:opacity-80 transition-opacity duration-150';

    const variantClasses = {
        danger: 'bg-neutral-200 text-orange-500 dark:bg-neutral-800 dark:text-pink-400',
        warning: 'text-yellow-400 dark:text-yellow-400 dark:bg-neutral-800',
        primary: 'text-sky-400 dark:text-sky-400 dark:bg-neutral-800',
    };

    return (
        <button {...props} className={`${baseClasses} ${variantClasses[variant]} ${className}`}>
            {children}
        </button>
    );
}
