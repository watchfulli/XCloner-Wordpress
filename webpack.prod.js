// Webpack uses this to work with directories
const path = require("path");
const TerserPlugin = require("terser-webpack-plugin");
const webpack = require('webpack');

// This is the main configuration object.
// Here you write different options and tell Webpack what to do
module.exports = {
  // Path to your entry point. From this file Webpack will begin his work
  entry: {
    index: "./xcloner-backup-and-restore/admin/js/index.js",
  },

  // Path and filename of your result bundle.
  // Webpack will bundle all JavaScript into this file
  output: {
    path: path.resolve(__dirname, "xcloner-backup-and-restore"),
    filename: "./admin/js/[name].min.js",
  },

  optimization: {
    minimize: true,
    minimizer: [new TerserPlugin(
        {
          extractComments: {
            banner: (licenseFile) => {
              return `\n
              For license information please see ${path.basename(licenseFile)}\n
              To view source code please see https://github.com/watchfulli/XCloner-Wordpress/\n`;
            },
          }
        }
    )],
  },

  plugins: [
    new webpack.BannerPlugin('You can review the source code here: https://github.com/watchfulli/XCloner-Wordpress/')
  ],
  mode: "production",
  devtool: 'source-map',
};
