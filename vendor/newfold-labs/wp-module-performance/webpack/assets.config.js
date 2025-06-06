// webpack/assets.config.js

const path = require('path');
const glob = require('glob');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

// Absolute paths for source and output directories (based on project root)
const assetsPath = path.resolve(process.cwd(), 'assets');
const buildPath = path.resolve(process.cwd(), 'build/assets');

module.exports = {
	// Set mode to production to enable optimizations like minification
	mode: 'production',

	// Dynamically create multiple entry points from JS files in the assets folder
	entry: glob.sync(path.join(assetsPath, '**/*.js')).reduce((entries, file) => {
		// Strip path and extension to create the output file name
		const name = path
			.relative(assetsPath, file)
			.replace(/(\.min)?\.js$/, ''); // Prevent double .min if already minified

		entries[name] = file; // e.g., 'image/image-bulk-optimizer': './assets/image/image-bulk-optimizer.js'
		return entries;
	}, {}),

	// Output compiled files to the build folder
	output: {
		path: buildPath,
		filename: '[name].min.js', // e.g., image/image-bulk-optimizer.min.js
	},

	module: {
		rules: [
			{
				// Handle all .css imports using PostCSS and MiniCssExtractPlugin
				test: /\.css$/i,
				use: [
					MiniCssExtractPlugin.loader, // Extract CSS to separate files
					'css-loader',                // Resolve @import and url() in CSS
				],
			},
		],
	},

	optimization: {
		// Enable minification
		minimize: true,
		minimizer: [
			// Minify JS
			new TerserPlugin({
				terserOptions: {
					compress: {
						drop_console: true, // Remove console.* calls
					},
					format: {
						comments: false,    // Remove comments
					},
				},
				extractComments: false, // Avoid generating separate LICENSE.txt files
			}),

			// Minify CSS
			new CssMinimizerPlugin(),
		],
	},

	plugins: [
		// Output CSS files with the same `[name].min.css` pattern
		new MiniCssExtractPlugin({
			filename: '[name].min.css', // e.g., image/image-bulk-optimizer.min.css
		}),
	],

	resolve: {
		// Allow imports without specifying these extensions
		extensions: ['.js', '.css'],
	},
};
