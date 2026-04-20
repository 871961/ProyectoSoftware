module.exports = {
    // Tailwind v2 expects `purge` to remove unused styles in production
    purge: [
        './static/**/*.html',
        './static/js/**/*.js',
        './src/**/*.js'
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
