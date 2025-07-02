import { PropsWithChildren } from 'react';

interface FormContainerProps {
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
}

export default function FormContainer({ 
    children, 
    maxWidth = 'xl' 
}: PropsWithChildren<FormContainerProps>) {
    const maxWidthClasses = {
        sm: 'max-w-sm',
        md: 'max-w-md', 
        lg: 'max-w-lg',
        xl: 'max-w-xl',
        '2xl': 'max-w-2xl'
    };

    return (
        <div className="py-8">
            <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <section className={maxWidthClasses[maxWidth]}>
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}