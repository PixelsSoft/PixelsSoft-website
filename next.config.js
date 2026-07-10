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
  images: {
    domains: [
      "cdn.sanity.io",
      "admin.pixelssoft.com",
      "pixelssoft.com",
      "www.pixelssoft.com",
      "localhost",
      "127.0.0.1",
    ],
    unoptimized: true,
  },
};
