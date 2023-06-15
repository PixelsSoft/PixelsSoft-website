const path = require( "path" );

module.exports = {
  reactStrictMode: true,
  sassOptions: {
    includePaths: [path.join( __dirname, "css" )],
  },
  trailingSlash: true,
  devIndicators: {
    buildActivity: false,
  },
  eslint: {
    ignoreDuringBuilds: false,
  },

  webpack: ( config, { dev, isServer } ) => {
    if ( !dev && !isServer ) {
      Object.assign( config.resolve.alias, {
        react: 'preact/compat',
        'react-dom/test-utils': 'preact/test-utils',
        'react-dom': 'preact/compat',
      } );
    }
    return config;
  },
};

