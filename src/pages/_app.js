import React from "react";
import Head from "next/head";
import Script from "next/script";
import dynamic from "next/dynamic";
import ScrollToTop from "../components/Scroll-to-top";
import LoadingScreen from "../components/Loading-Screen";
import { organizationSchema, websiteSchema } from "../components/SEO";
import "../styles/globals.css";
import "../styles/blog-grid.css";

const GoogleServices = dynamic(() => import("../components/GoogleServices"), {
  ssr: false,
});

const CookieConsent = dynamic(() => import("../components/CookieConsent"), {
  ssr: false,
});

const Cursor = dynamic(() => import("../components/Cursor"), {
  ssr: false,
});

const TawkWidget = dynamic(() => import("../components/TawkWidget"), {
  ssr: false,
});

function MyApp({ Component, pageProps }) {
  const [googleSettings, setGoogleSettings] = React.useState(null);
  const [consentGiven, setConsentGiven] = React.useState(false);
  const [mounted, setMounted] = React.useState(false);
  const [enableCursor, setEnableCursor] = React.useState(false);
  const [enableChat, setEnableChat] = React.useState(false);
  const [tawkIds, setTawkIds] = React.useState({
    propertyId: "648864e494cf5d49dc5d6a94",
    widgetId: "1h2qck7tf",
  });

  React.useEffect(() => {
    setMounted(true);
    const accepted =
      localStorage.getItem("pixels_soft_cookie_consent") === "accepted";
    setConsentGiven(accepted);

    const finePointer = window.matchMedia("(hover: hover) and (pointer: fine)");
    const isTouch = window.matchMedia("(pointer: coarse)").matches;
    setEnableCursor(finePointer.matches && !isTouch);

    import("../lib/api")
      .then(({ getSettings, getGoogleSettings }) => {
        getSettings()
          .then((settings) => {
            if (settings?.tawk_property_id && settings?.tawk_widget_id) {
              setTawkIds({
                propertyId: settings.tawk_property_id,
                widgetId: settings.tawk_widget_id,
              });
            }
          })
          .catch(() => {});

        if (accepted) {
          getGoogleSettings()
            .then(setGoogleSettings)
            .catch(() => setGoogleSettings(null));
        }
      })
      .catch(() => {});

    // Defer chat until idle / first interaction (helps LCP & TBT)
    let loaded = false;
    const loadChat = () => {
      if (loaded) return;
      loaded = true;
      setEnableChat(true);
      cleanup();
    };
    const cleanup = () => {
      window.removeEventListener("scroll", loadChat);
      window.removeEventListener("pointerdown", loadChat);
      window.removeEventListener("keydown", loadChat);
    };
    window.addEventListener("scroll", loadChat, { once: true, passive: true });
    window.addEventListener("pointerdown", loadChat, { once: true });
    window.addEventListener("keydown", loadChat, { once: true });
    const idleId =
      typeof window.requestIdleCallback === "function"
        ? window.requestIdleCallback(loadChat, { timeout: 6000 })
        : window.setTimeout(loadChat, 5000);

    return () => {
      cleanup();
      if (typeof window.cancelIdleCallback === "function") {
        window.cancelIdleCallback(idleId);
      } else {
        clearTimeout(idleId);
      }
    };
  }, []);

  const handleConsent = () => {
    setConsentGiven(true);
    import("../lib/api")
      .then(({ getGoogleSettings }) =>
        getGoogleSettings()
          .then(setGoogleSettings)
          .catch(() => setGoogleSettings(null))
      )
      .catch(() => {});
  };

  return (
    <>
      <Head>
        <meta
          name="viewport"
          content="width=device-width, initial-scale=1, viewport-fit=cover"
        />
        <meta name="theme-color" content="#0c0f16" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
        <link rel="icon" href="/img/favicon.png" />
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{
            __html: JSON.stringify([organizationSchema, websiteSchema]),
          }}
        />
      </Head>

      {enableCursor ? <Cursor /> : null}
      <LoadingScreen />
      <ScrollToTop />
      <Component {...pageProps} />

      {mounted && consentGiven && (
        <GoogleServices settings={googleSettings} />
      )}

      {/* Splitting/WOW disabled — they hide text (opacity 0) on mobile */}
      <Script strategy="lazyOnload" id="initWow" src="/js/initWow.js" />

      {mounted && enableChat && (
        <TawkWidget
          propertyId={tawkIds.propertyId}
          widgetId={tawkIds.widgetId}
        />
      )}
      {mounted && <CookieConsent onAccept={handleConsent} />}
    </>
  );
}

export default MyApp;
