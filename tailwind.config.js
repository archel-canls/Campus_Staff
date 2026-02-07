/** @type {import('tailwindcss').Config} */
export default {
    content: [
      "./resources/**/*.blade.php",
      "./resources/**/*.js",
      "./resources/**/*.vue",
    ],
    theme: {
      extend: {
        colors: {
          'cdi-blue': '#003366',
          'cdi-orange': '#FF8C00',
        }
      },
    },
    plugins: [],
  }