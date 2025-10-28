// Common CSS class constants used throughout the application

export const INPUT_CLASSES = {
    base: 'mt-1 block w-full',
    textarea: 'mt-1 block w-full',
} as const;

export const TEXT_CLASSES = {
    smallGray: 'text-sm text-gray-600 dark:text-gray-400',
    mediumGray: 'text-gray-600 dark:text-gray-400',
    error: 'text-sm text-red-600 dark:text-red-400',
} as const;

export const CARD_CLASSES = {
    base: 'p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg',
    section: 'py-8',
    container: 'max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6',
} as const;

export const BUTTON_CLASSES = {
    link: 'underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800',
} as const;
