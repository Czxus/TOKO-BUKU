import forms from '@tailwindcss/forms';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                hijau: { DEFAULT: '#0F4C45', tua: '#0A3B36' },
                krem: '#F7F4EE',
                amber: { buku: '#F2B45A' },
                tinta: '#1B2A2A',
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                serif: ['Fraunces', 'Georgia', 'serif'],
            },
        },
    },
    plugins: [forms],
};
