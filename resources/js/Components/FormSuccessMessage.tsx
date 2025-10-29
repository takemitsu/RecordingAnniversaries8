import { Transition } from '@headlessui/react';

interface FormSuccessMessageProps {
    show: boolean;
    message?: string;
}

export default function FormSuccessMessage({ show, message = '保存しました。' }: FormSuccessMessageProps) {
    return (
        <Transition
            show={show}
            enter="transition ease-in-out"
            enterFrom="opacity-0"
            leave="transition ease-in-out"
            leaveTo="opacity-0"
        >
            <p className="text-sm text-gray-600 dark:text-gray-400">{message}</p>
        </Transition>
    );
}
