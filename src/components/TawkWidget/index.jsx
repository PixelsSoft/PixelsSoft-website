import React from "react";

let tawkInjected = false;

/**
 * Loads Tawk.to only in production. Their embed throws i18next errors
 * that Next.js shows as a blocking red overlay in development.
 */
const TawkWidget = ({ propertyId, widgetId }) => {
  React.useEffect(() => {
    if (process.env.NODE_ENV !== "production") return;
    if (!propertyId || !widgetId || typeof window === "undefined") return;
    if (tawkInjected || document.getElementById("tawk-embed-script")) {
      tawkInjected = true;
      return;
    }

    tawkInjected = true;
    window.Tawk_API = window.Tawk_API || {};
    window.Tawk_LoadStart = new Date();

    const suppressTawkNoise = (event) => {
      const msg = String(event?.message || event?.reason || "");
      const src = String(event?.filename || "");
      const fromTawk =
        src.includes("tawk.to") ||
        src.includes("tw-chunk") ||
        src.includes("tw-vendor") ||
        msg.includes("$_Tawk") ||
        msg.includes("i18next");
      if (fromTawk) {
        event.preventDefault?.();
        event.stopImmediatePropagation?.();
        return true;
      }
      return false;
    };

    window.addEventListener("error", suppressTawkNoise, true);
    window.addEventListener("unhandledrejection", suppressTawkNoise, true);

    const timer = window.setTimeout(() => {
      try {
        if (document.getElementById("tawk-embed-script")) return;
        const script = document.createElement("script");
        script.id = "tawk-embed-script";
        script.async = true;
        script.src = `https://embed.tawk.to/${propertyId}/${widgetId}`;
        script.charset = "UTF-8";
        document.body.appendChild(script);
      } catch (_) {
        tawkInjected = false;
      }
    }, 3000);

    return () => {
      window.clearTimeout(timer);
      window.removeEventListener("error", suppressTawkNoise, true);
      window.removeEventListener("unhandledrejection", suppressTawkNoise, true);
    };
  }, [propertyId, widgetId]);

  return null;
};

export default TawkWidget;
