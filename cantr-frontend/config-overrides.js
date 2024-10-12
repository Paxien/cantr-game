module.exports = {
  webpack: function(config, env) {
    if (env === "development") {
      config.optimization.splitChunks = {
        cacheGroups: {
          default: false,
        },
      };
      config.optimization.runtimeChunk = false;
    }
    return config;
  },
  // temporarily it's necessary to overwrite devserver configuration to be able to import JS and CSS
  // directly into Cantr's template. Without that it's breaking CORS
  devServer: function(configFunction) {
    return function(proxy, allowedHost) {
      const config = configFunction(proxy, allowedHost);
      config.headers = {
        "Access-Control-Allow-Origin": "*",
        "Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, PATCH, OPTIONS",
      };
      return config;
    };
  },
};