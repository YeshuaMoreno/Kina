import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            // Paleta Nebulosa de Kina
            colors: {
                malva: '#4A4E69',
                lavanda: '#9A8C98',
                rosa: '#C9ADA7', // rosa-palo
                tiza: '#F2E9E4',
                dark: '#22223B',
                error: '#B0413E',
                success: '#5B8A72',
            },

            fontFamily: {
                // Cuerpo / interfaz -> Nunito
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
                // Títulos -> Lora
                serif: ['Lora', ...defaultTheme.fontFamily.serif],
                // Familias explícitas
                lora: ['Lora', ...defaultTheme.fontFamily.serif],
                nunito: ['Nunito', ...defaultTheme.fontFamily.sans],
                inter: ['Inter', ...defaultTheme.fontFamily.sans],
            },

            borderRadius: {
                xl: '0.9rem',
                '2xl': '1.25rem',
            },
        },
    },

    plugins: [forms],
};
