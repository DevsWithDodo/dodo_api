/** @type {import('tailwindcss').Config} */
export default {
  content: ["./resources/js/**/*.{js,ts,jsx,tsx,mdx}"],
  theme: {
    extend: {
      colors: {
        dodo: {
          blue: '#1398B5',
          yellow: '#F7C000',
        },
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
} 