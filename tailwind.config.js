const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    purge: ['./storage/framework/views/*.php',
            './resources/views/**/*.blade.php',
            './resources/js/**/*.vue'],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Roboto Slab', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                primary: '#B90C11'
            },
        },
    },

    variants: {
        extend: {
            opacity: ['disabled'],
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
