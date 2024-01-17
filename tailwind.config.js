
/** @type {import('tailwindcss').Config} */
import preset from './vendor/filament/support/tailwind.config.preset'

export default {
  darkMode: 'class',
  content: [
    "./app/Filament/**/*.php",
    "./app/Livewire/**/*.php",
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./vendor/filament/**/*.blade.php",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}

