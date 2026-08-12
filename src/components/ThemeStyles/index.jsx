/* eslint-disable @next/next/no-css-tags */
import React from "react";
import Head from "next/head";

/**
 * Loads theme CSS without chained @imports (those caused long critical-path chains).
 * Core layout CSS is sync so the site never renders unstyled if JS is late.
 */
const ThemeStyles = ({ themeHref }) => {
  return (
    <Head>
      <link rel="stylesheet" href="/css/bootstrap.min.css" />
      <link rel="stylesheet" href={themeHref} />
      <link rel="stylesheet" href="/css/font-awesome.min.css" />
      <link rel="stylesheet" href="/css/pe-icon.min.css" />
      <link rel="stylesheet" href="/css/ionicons.min.css" />
      <link rel="stylesheet" href="/css/animate.css" />
      <link rel="stylesheet" href="/css/font-display.css" />
    </Head>
  );
};

export default ThemeStyles;
