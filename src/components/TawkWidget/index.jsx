import React from "react";

const TawkWidget = ({ propertyId, widgetId }) => {
  const loadedRef = React.useRef(false);

  React.useEffect(() => {
    if (!propertyId || !widgetId || loadedRef.current) return;
    if (typeof window === "undefined") return;

    loadedRef.current = true;
    window.Tawk_API = window.Tawk_API || {};
    window.Tawk_LoadStart = new Date();

    const script = document.createElement("script");
    script.async = true;
    script.src = `https://embed.tawk.to/${propertyId}/${widgetId}`;
    script.charset = "UTF-8";
    script.setAttribute("crossorigin", "*");
    document.body.appendChild(script);
  }, [propertyId, widgetId]);

  return null;
};

export default TawkWidget;
