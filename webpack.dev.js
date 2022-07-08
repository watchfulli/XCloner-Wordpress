// Webpack uses this to work with directories
const path = require("path");
const TerserPlugin = require("terser-webpack-plugin");

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
    path: path.resolve(__dirname, "xcloner-backup-and-restore/dist"),
    filename: "./admin/js/[name].min.js",
  },

  optimization: {
    minimize: false,
    minimizer: [new TerserPlugin()],
  },

  // Default mode for Webpack is production.
  // Depending on mode Webpack will apply different things
  // on final bundle. For now we don't need production's JavaScript
  // minifying and other thing so let's set mode to development
  //mode: 'development'
  mode: "development",
  devtool: 'inline-source-map',
};
