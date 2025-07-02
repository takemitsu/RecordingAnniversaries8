import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import TextareaInput from '@/Components/TextareaInput';
import InputError from '@/Components/InputError';
import { INPUT_CLASSES } from '@/constants/styles';
import { InputHTMLAttributes, TextareaHTMLAttributes } from 'react';

interface BaseFormFieldProps {
    id: string;
    label: string;
    error?: string;
    className?: string;
}

interface TextInputFieldProps extends BaseFormFieldProps {
    type?: 'text';
    inputProps?: Omit<InputHTMLAttributes<HTMLInputElement>, 'id' | 'className'>;
}

interface TextareaFieldProps extends BaseFormFieldProps {
    type: 'textarea';
    inputProps?: Omit<TextareaHTMLAttributes<HTMLTextAreaElement>, 'id' | 'className'>;
}

type FormFieldProps = TextInputFieldProps | TextareaFieldProps;

export default function FormField({ 
    id, 
    label, 
    error, 
    type = 'text', 
    inputProps = {},
    className = '' 
}: FormFieldProps) {
    return (
        <div className={className}>
            <InputLabel htmlFor={id} value={label} />
            
            {type === 'textarea' ? (
                <TextareaInput
                    id={id}
                    className={INPUT_CLASSES.textarea}
                    {...(inputProps as TextareaHTMLAttributes<HTMLTextAreaElement>)}
                />
            ) : (
                <TextInput
                    id={id}
                    className={INPUT_CLASSES.base}
                    {...(inputProps as InputHTMLAttributes<HTMLInputElement>)}
                />
            )}
            
            <InputError className="mt-2" message={error} />
        </div>
    );
}