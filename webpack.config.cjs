// webpack with standard nextcloud config
const path = require('path')
const webpack = require('webpack')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
	...webpackConfig.entry,
	account: path.join(__dirname, 'src', 'js', 'account.js'),
	devices: path.join(__dirname, 'src', 'js', 'devices.js'),
	nmcsettings: path.join(__dirname, 'src', 'js', 'nmcsettings.js'),
	personal: path.join(__dirname, 'src', 'js', 'personal.js'),
	sessions: path.join(__dirname, 'src', 'js', 'sessions.js'),
	themes: path.join(__dirname, 'src', 'js', 'themes.js'),
}

// Workaround for https://github.com/nextcloud/webpack-vue-config/pull/432 causing problems with nextcloud-vue-collections
webpackConfig.resolve.alias = {}

// Remove the problematic node-polyfill-webpack-plugin
webpackConfig.plugins = webpackConfig.plugins.filter(plugin => {
	return plugin.constructor.name !== 'NodePolyfillPlugin'
})

// Add ProvidePlugin for Buffer
webpackConfig.plugins.push(
	new webpack.ProvidePlugin({
		Buffer: ['buffer', 'Buffer'],
	})
)

// Configure resolve fallbacks
webpackConfig.resolve.fallback = {
	...webpackConfig.resolve.fallback,
	buffer: require.resolve('buffer/'),
	path: require.resolve('path-browserify'),
	stream: require.resolve('stream-browserify'),
	crypto: require.resolve('crypto-browserify'),
	util: require.resolve('util'),
}

module.exports = webpackConfig
