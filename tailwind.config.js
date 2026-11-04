const defaultTheme = require('tailwindcss/defaultTheme');
const colors = require('tailwindcss/colors');

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                gray: {
                    ...colors.gray,
                    950: '#0a0a0a',
                },
                blue: {
                    ...colors.blue,
                    950: '#081a36',
                },
                red: {
                    ...colors.red,
                    950: '#2b0707',
                },
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
