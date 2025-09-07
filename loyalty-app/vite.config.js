import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'


// https://vite.dev/config/
export default defineConfig({
  plugins: [
    react(),
    tailwindcss(),
  ],
  test: {
    globals: true,       // use global test functions like `describe`, `it`, `expect`
    environment: "jsdom", // simulate browser environment
    setupFiles: "./src/setupTests.js", // optional, for jest-dom
    coverage: {
      reporter: ["text", "json", "html"], // generate coverage report
    },
  },
})
