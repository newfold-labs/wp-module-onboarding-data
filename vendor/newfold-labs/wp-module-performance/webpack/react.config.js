// webpack/react.config.js

const path = require('path');
const { merge } = require('webpack-merge');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

const customConfig = {
	entry: {
		performance: path.resolve(process.cwd(), 'src/performance.js'), // Main React entry point
	},
	output: {
		path: path.resolve(process.cwd(), 'build/performance'),
		filename: 'performance.min.js', // Output JS
	},
	plugins: [
		new MiniCssExtractPlugin({
			filename: 'performance.min.css', // Output CSS
		}),
	],
};

module.exports = merge(defaultConfig, customConfig);
