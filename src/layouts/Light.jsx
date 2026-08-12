/* eslint-disable @next/next/no-css-tags */
import React from "react";
import ThemeStyles from "../components/ThemeStyles";

const LightTheme = ({ children, bdOn }) => {
  React.useEffect(() => {
    if (!bdOn) return undefined;
    document.querySelector("body")?.classList.add("bd-dark");
    return () => {
      document.querySelector("body")?.classList.remove("bd-dark");
    };
  }, [bdOn]);

  return (
    <>
      <ThemeStyles themeHref="/css/light.css" />
      {children}
    </>
  );
};

export default LightTheme;
