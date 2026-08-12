/* eslint-disable @next/next/no-css-tags */
import React from "react";
import ThemeStyles from "../components/ThemeStyles";

const DarkTheme = ({ children }) => {
  return (
    <>
      <ThemeStyles themeHref="/css/dark.css" />
      {children}
    </>
  );
};

export default DarkTheme;
