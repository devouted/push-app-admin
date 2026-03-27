import react from "@vitejs/plugin-react";
import { defineConfig } from "vite";

export default defineConfig({
	plugins: [react()],
	server: {
		host: "0.0.0.0",
		port: 5173,
		strictPort: true,
		allowedHosts: ["apache", "localhost"],
		hmr: {
			clientPort: 5173,
		},
	},
	css: {
		preprocessorOptions: {
			scss: {
				silenceDeprecations: ['import'],
			},
		},
	},
});
