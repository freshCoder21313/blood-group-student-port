import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb', // Brand Primary
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a', // Deep Royal
                    950: '#172554',
                },
                secondary: colors.emerald, // Growth/Success
                accent: colors.amber,      // Warnings/Action
                danger: colors.rose,       // Errors
                gray: colors.slate,        // Professional Neutrals
            },
        },
    },

    plugins: [forms],
};
