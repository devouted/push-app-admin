import tailwindcss from "@tailwindcss/postcss";
import daisyui from "daisyui";

export default {
	content: ["./index.html", "./src/**/*.{js,ts,jsx,tsx}"],
	theme: {
		extend: {
			colors: {
				// Bootstrap color palette
				blue: {
					100: '#cfe2ff',
					200: '#9ec5fe',
					300: '#6ea8fe',
					400: '#3d8bfd',
					500: '#0d6efd',
					600: '#0a58ca',
					700: '#084298',
					800: '#052c65',
					900: '#031633',
				},
				indigo: {
					500: '#6610f2',
					600: '#520dc2',
				},
				purple: {
					500: '#6f42c1',
					600: '#59359a',
				},
				pink: {
					500: '#d63384',
					600: '#ab296a',
				},
				red: {
					500: '#dc3545',
					600: '#b02a37',
				},
				orange: {
					500: '#fd7e14',
					600: '#ca6510',
				},
				yellow: {
					500: '#ffc107',
					600: '#cc9a06',
				},
				green: {
					500: '#198754',
					600: '#146c43',
				},
				teal: {
					500: '#20c997',
					600: '#1aa179',
				},
				cyan: {
					500: '#0dcaf0',
					600: '#0aa2c0',
				},
			}
		},
	},
	plugins: [tailwindcss, daisyui],
	daisyui: {
		themes: [
			{
				light: {
					"primary": "#0d6efd",
					"secondary": "#6f42c1",
					"accent": "#0dcaf0",
					"neutral": "#212529",
					"base-100": "#ffffff",
					"base-200": "#f8f9fa",
					"base-300": "#e9ecef",
					"info": "#0dcaf0",
					"success": "#198754",
					"warning": "#ffc107",
					"error": "#dc3545",
				},
			},
		],
	},
};
