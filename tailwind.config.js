<<<<<<< HEAD
const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
=======
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
>>>>>>> dev
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
<<<<<<< HEAD
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
=======
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
>>>>>>> dev
            },
        },
    },

<<<<<<< HEAD
    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
=======
    plugins: [forms, typography],
>>>>>>> dev
};
