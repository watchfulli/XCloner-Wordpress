// Webpack uses this to work with directories
const path = require("path");
const TerserPlugin = require("terser-webpack-plugin");

// This is the main configuration object.
// Here you write different options and tell Webpack what to do
module.exports = {
  // Path to your entry point. From this file Webpack will begin his work
  //entry: './admin/js/index.js',
  entry: {
    index: "./admin/js/index.js",
    /*xcloner_admin: "./admin/js/xcloner-admin.js",
    xcloner_backup_class: "./admin/js/xcloner-backup-class.js",
    xcloner_manage_backups_class: "./admin/js/xcloner-manage-backups-class.js",
    xcloner_remote_storage_class: "./admin/js/xcloner-remote-storage-class.js",
    xcloner_restore_class: "./admin/js/xcloner-restore-class.js",
    xcloner_scheduler_class: "./admin/js/xcloner-scheduler-class.js",
    */
  },

  // Path and filename of your result bundle.
  // Webpack will bundle all JavaScript into this file
  output: {
    path: path.resolve(__dirname, "dist"),
    filename: "./admin/js/[name].min.js",
  },

  optimization: {
    minimize: true,
    minimizer: [new TerserPlugin()],
  },
  
  // Default mode for Webpack is production.
  // Depending on mode Webpack will apply different things
  // on final bundle. For now we don't need production's JavaScript
  // minifying and other thing so let's set mode to development
  //mode: 'development'
  mode: "production",
  devtool: 'source-map',
};
