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
                // Verre liquide iOS : ombre portée douce + halo interne lumineux.
                glass: '0 8px 32px -8px rgba(15,15,15,0.16), inset 0 1px 0 0 rgba(255,255,255,0.55)',
                'glass-lg': '0 24px 64px -16px rgba(15,15,15,0.28), inset 0 1px 0 0 rgba(255,255,255,0.6)',
            },
            backdropBlur: {
                xs: '2px',
                glass: '20px',
            },
            transitionTimingFunction: {
                // Ressort doux type iOS (overshoot léger).
                spring: 'cubic-bezier(0.34, 1.56, 0.64, 1)',
                ios: 'cubic-bezier(0.16, 1, 0.3, 1)',
            },
            keyframes: {
                rise: {
                    from: { opacity: '0', transform: 'translateY(14px) scale(0.98)' },
                    to: { opacity: '1', transform: 'translateY(0) scale(1)' },
                },
                pop: {
                    '0%': { opacity: '0', transform: 'scale(0.9)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                'aurora-1': {
                    '0%,100%': { transform: 'translate(0,0) scale(1)' },
                    '50%': { transform: 'translate(6%,8%) scale(1.15)' },
                },
                'aurora-2': {
                    '0%,100%': { transform: 'translate(0,0) scale(1.1)' },
                    '50%': { transform: 'translate(-8%,-6%) scale(1)' },
                },
                shimmer: {
                    '100%': { transform: 'translateX(100%)' },
                },
                float: {
                    '0%,100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-6px)' },
                },
            },
            animation: {
                rise: 'rise 0.6s cubic-bezier(0.16,1,0.3,1) both',
                pop: 'pop 0.4s cubic-bezier(0.34,1.56,0.64,1) both',
                'aurora-1': 'aurora-1 18s ease-in-out infinite',
                'aurora-2': 'aurora-2 22s ease-in-out infinite',
                shimmer: 'shimmer 2.4s ease-in-out infinite',
                float: 'float 5s ease-in-out infinite',
            },
            fontSize: {
                heading: ['2.125rem', { lineHeight: '1.1', letterSpacing: '-0.02em' }], // 34px
                title: ['1.75rem', { lineHeight: '1.15', letterSpacing: '-0.01em' }],    // 28px
            },
        },
    },
    plugins: [],
};
