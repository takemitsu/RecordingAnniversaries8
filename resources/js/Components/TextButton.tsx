import React, {ButtonHTMLAttributes} from 'react';

export default function TextButton({ className = '', disabled, children, ...props }: ButtonHTMLAttributes<HTMLButtonElement>) {

    return (
        <button
            {...props}
            className={
                `inline-flex items-center px-2 py-1 border border-transparent rounded-md text-sm text-gray text-gray-600 transition ease-in-out duration-150 ${
                    disabled && 'opacity-25'
                } ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
