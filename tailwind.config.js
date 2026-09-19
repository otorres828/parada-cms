/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    darkMode: "class",
    theme: {
        extend: {
            "colors": {
                "inverse-surface": "#e5e2e1",
                "on-primary-fixed-variant": "#753400",
                "on-tertiary": "#00391a",
                "on-background": "#e5e2e1",
                "on-primary-fixed": "#321200",
                "on-primary": "#522300",
                "surface-bright": "#3a3939",
                "primary": "#FC4B08",
                "outline": "#a78b7c",
                "on-secondary-fixed": "#410003",
                "on-surface-variant": "#e0c0af",
                "error-container": "#93000a",
                "surface-container-low": "#1c1b1b",
                "on-secondary": "#690007",
                "secondary-container": "#cd021a",
                "on-tertiary-container": "#00411e",
                "on-tertiary-fixed": "#00210d",
                "surface-container": "#201f1f",
                "primary-container": "#ff7a00",
                "tertiary-fixed": "#77fca3",
                "secondary": "#ffb4ac",
                "primary-fixed": "#ffdbc8",
                "secondary-fixed-dim": "#ffb4ac",
                "on-secondary-container": "#ffdcd8",
                "surface-container-high": "#2a2a2a",
                "surface-dim": "#131313",
                "surface-container-lowest": "#0e0e0e",
                "background": "#131313",
                "on-surface": "#e5e2e1",
                "on-error-container": "#ffdad6",
                "secondary-fixed": "#ffdad6",
                "error": "#ffb4ab",
                "surface-container-highest": "#353534",
                "surface-variant": "#353534",
                "tertiary-container": "#27b766",
                "on-error": "#690005",
                "on-primary-container": "#5c2800",
                "primary-fixed-dim": "#ffb68b",
                "inverse-on-surface": "#313030",
                "on-secondary-fixed-variant": "#93000f",
                "inverse-primary": "#994700",
                "tertiary": "#59df89",
                "on-tertiary-fixed-variant": "#005228",
                "surface-tint": "#ffb68b",
                "tertiary-fixed-dim": "#59df89",
                "outline-variant": "#584235",
                "surface": "#131313"
            },
            "borderRadius": {
                "DEFAULT": "0.125rem",
                "lg": "0.25rem",
                "xl": "0.5rem",
                "full": "0.75rem"
            },
            "spacing": {
                "lg": "24px",
                "base": "4px",
                "margin-desktop": "48px",
                "xs": "4px",
                "md": "16px",
                "xl": "40px",
                "margin-mobile": "16px",
                "sm": "8px",
                "gutter": "20px",
                "container-margin": "24px",
                "panel-padding": "20px",
                "stack-gap": "12px"
            },
            "fontFamily": {
                "headline-md": [
                    "Anybody"
                ],
                "body-lg": [
                    "Lexend"
                ],
                "headline-lg-mobile": [
                    "Anybody"
                ],
                "display-lg": [
                    "Anybody"
                ],
                "body-md": [
                    "Lexend"
                ],
                "headline-lg": [
                    "Anybody"
                ],
                "label-md": [
                    "Lexend"
                ]
            },
            "fontSize": {
                "headline-md": [
                    "24px",
                    {
                        "lineHeight": "1.3",
                        "fontWeight": "700"
                    }
                ],
                "body-lg": [
                    "18px",
                    {
                        "lineHeight": "1.6",
                        "fontWeight": "400"
                    }
                ],
                "headline-lg-mobile": [
                    "32px",
                    {
                        "lineHeight": "1.2",
                        "fontWeight": "700"
                    }
                ],
                "display-lg": [
                    "64px",
                    {
                        "lineHeight": "1.1",
                        "letterSpacing": "-0.04em",
                        "fontWeight": "800"
                    }
                ],
                "body-md": [
                    "16px",
                    {
                        "lineHeight": "1.5",
                        "fontWeight": "400"
                    }
                ],
                "headline-lg": [
                    "40px",
                    {
                        "lineHeight": "1.2",
                        "letterSpacing": "-0.02em",
                        "fontWeight": "700"
                    }
                ],
                "label-md": [
                    "14px",
                    {
                        "lineHeight": "1.2",
                        "letterSpacing": "0.05em",
                        "fontWeight": "600"
                    }
                ],
                "label-sm": ["12px", {
                    "lineHeight": "16px",
                    "fontWeight": "500"
                }]
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms')({
            strategy: 'class', // Esto evita conflictos agresivos con estilos globales
        }),
    ],
}
