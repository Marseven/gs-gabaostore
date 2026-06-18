import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Système de design "Soft Lime CRM"
                ink: '#0A0A0A',          // noir d'encre — primaire
                canvas: '#E7E7E4',       // fond gris clair chaud
                surface: '#FFFFFF',      // cartes
                lime: {
                    DEFAULT: '#C7F33C',  // vert lime — accent énergique
                    pale: '#E1F2AE',     // lime pâle — fonds doux
                    600: '#A8D62E',
                },
                muted: '#7A7A75',        // texte secondaire
            },
            borderRadius: {
                card: '1.75rem',         // 28px — cartes généreusement arrondies
                pill: '999px',
            },
            boxShadow: {
                soft: '0 2px 24px -8px rgba(15, 15, 15, 0.10)',
                'soft-lg': '0 12px 48px -12px rgba(15, 15, 15, 0.18)',
                pill: '0 1px 2px rgba(0,0,0,0.06)',
            },
            fontSize: {
                heading: ['2.125rem', { lineHeight: '1.1', letterSpacing: '-0.02em' }], // 34px
                title: ['1.75rem', { lineHeight: '1.15', letterSpacing: '-0.01em' }],    // 28px
            },
        },
    },
    plugins: [],
};
