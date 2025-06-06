const { TAILWINDCSS_PRESET } = require('@newfold/ui-component-library');

module.exports = {
	presets: [TAILWINDCSS_PRESET],
	content: [
		...TAILWINDCSS_PRESET.content,
		'./src/**/*.{js,jsx}',
		'./components/**/*.{js,jsx}',
	],
	theme: {
		extend: {
			colors: {
				primary: {
					DEFAULT: 'var(--nfd-color-primary)',
					dark: 'var(--nfd-color-primary-dark)',
					light: 'var(--nfd-color-primary-light)',
					lighter: 'var(--nfd-color-primary-lighter)',
				},
				secondary: {
					DEFAULT: 'var(--nfd-color-secondary)',
					dark: 'var(--nfd-color-secondary-dark)',
					light: 'var(--nfd-color-secondary-light)',
					lighter: 'var(--nfd-color-secondary-lighter)',
				},
				title: 'var(--nfd-color-title)',
				body: 'var(--nfd-color-body)',
			},
		},
	}

};
