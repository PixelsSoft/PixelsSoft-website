import React from "react";
import Head from "next/head";
import Script from "next/script";
import dynamic from "next/dynamic";
import Cursor from "../components/Cursor";
import ScrollToTop from "../components/Scroll-to-top";
import LoadingScreen from "../components/Loading-Screen";
import TawkWidget from "../components/TawkWidget";
import { organizationSchema, websiteSchema } from "../components/SEO";
import "../styles/globals.css";
import "../styles/blog-grid.css";

const GoogleServices = dynamic(() => import("../components/GoogleServices"), {
  ssr: false,
});

const CookieConsent = dynamic(() => import("../components/CookieConsent"), {
  ssr: false,
});

function MyApp({ Component, pageProps }) {
  const [googleSettings, setGoogleSettings] = React.useState(null);
  const [consentGiven, setConsentGiven] = React.useState(false);
  const [mounted, setMounted] = React.useState(false);
  const [tawkIds, setTawkIds] = React.useState({
    propertyId: "648864e494cf5d49dc5d6a94",
    widgetId: "1h2qck7tf",
  });

  React.useEffect(() => {
    setMounted(true);
    const accepted =
      localStorage.getItem("pixels_soft_cookie_consent") === "accepted";
    setConsentGiven(accepted);

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
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" href="/img/pixels-soft-logo.png" />
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{
            __html: JSON.stringify([organizationSchema, websiteSchema]),
          }}
        />
      </Head>
      <Cursor />
      <LoadingScreen />
      <ScrollToTop />
      <Component {...pageProps} />

      {mounted && consentGiven && (
        <GoogleServices settings={googleSettings} />
      )}

      <Script strategy="beforeInteractive" id="splitting" src="/js/splitting.min.js" />
      <Script strategy="lazyOnload" id="wow" src="/js/wow.min.js" />
      <Script strategy="lazyOnload" id="simpleParallax" src="/js/simpleParallax.min.js" />
      <Script strategy="lazyOnload" id="initWow" src="/js/initWow.js" />

      {mounted && (
        <>
          <TawkWidget
            propertyId={tawkIds.propertyId}
            widgetId={tawkIds.widgetId}
          />
          <CookieConsent onAccept={handleConsent} />
        </>
      )}
    </>
  );
}

export default MyApp;
