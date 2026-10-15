import type { Config } from "tailwindcss";

const config: Config = {
  content: ["./src/**/*.{ts,tsx,mdx}"],
  theme: {
    container: {
      center: true,
      padding: { DEFAULT: "1rem", sm: "1.5rem", lg: "2rem" },
      screens: { sm: "640px", md: "768px", lg: "1024px", xl: "1200px" },
    },
    extend: {
      colors: {
        // Manual da marca Drogacare
        brand: {
          navy: "#061e3b", // Azul Escuro
          blue: "#2775bb", // Azul
          sky: "#1ea8e0", // Azul Claro
          green: "#7bc58f", // Verde
          mint: "#c2e4d2", // Verde Claro
          forest: "#224e41", // Verde Escuro
        },
      },
      fontFamily: {
        sans: ["var(--font-raleway)", "Arial", "Helvetica", "sans-serif"],
      },
      boxShadow: {
        card: "0 1px 2px rgba(6,30,59,.06), 0 8px 24px -12px rgba(6,30,59,.18)",
      },
    },
  },
  plugins: [],
};

export default config;
