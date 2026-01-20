module.exports = {
    purge: ['./storage/framework/views/*.php',
            './resources/views/**/*.blade.php',
            './resources/js/**/*.vue'],

    theme: {
        extend: {
            fontFamily: {
                serif: ['Roboto Serif', 'ui-serif', 'Cambria', 'Times New Roman', 'Times', 'serif'],
            },
            colors: {
                primary: '#B90C11'
            },
            screens: {
                sm: '480px',
                md: '600px',
                lg: '960px',
                xl: '1280px',
                '2xl': '1536px',
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
