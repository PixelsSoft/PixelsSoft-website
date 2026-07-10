import React from "react";
import Head from "next/head";
import Script from "next/script";

const GoogleAnalytics = ({ measurementId }) => (
  <>
    <Script
      src={`https://www.googletagmanager.com/gtag/js?id=${measurementId}`}
      strategy="afterInteractive"
    />
    <Script id="google-analytics" strategy="afterInteractive">
      {`
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '${measurementId}');
      `}
    </Script>
  </>
);

const GoogleTagManager = ({ containerId }) => (
  <>
    <Script id="google-tag-manager" strategy="afterInteractive">
      {`
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','${containerId}');
      `}
    </Script>
    <noscript>
      <iframe
        src={`https://www.googletagmanager.com/ns.html?id=${containerId}`}
        height="0"
        width="0"
        style={{ display: "none", visibility: "hidden" }}
        title="Google Tag Manager"
      />
    </noscript>
  </>
);

const GoogleAdSense = ({ publisherId, slots = {} }) => (
  <>
    <Script
      async
      src={`https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${publisherId}`}
      crossOrigin="anonymous"
      strategy="afterInteractive"
    />
    {Object.entries(slots).map(([key, slotId]) => (
      <ins
        key={key}
        className="adsbygoogle"
        style={{ display: "block" }}
        data-ad-client={publisherId}
        data-ad-slot={slotId}
        data-ad-format="auto"
        data-full-width-responsive="true"
      />
    ))}
  </>
);

const GoogleSearchConsole = ({ verificationCode }) => (
  <Head>
    <meta name="google-site-verification" content={verificationCode} />
  </Head>
);

const GoogleServices = ({ settings = {} }) => {
  if (!settings || Object.keys(settings).length === 0) return null;

  return (
    <>
      {settings.analytics?.enabled && settings.analytics?.measurement_id && (
        <GoogleAnalytics measurementId={settings.analytics.measurement_id} />
      )}
      {settings.gtm?.enabled && settings.gtm?.container_id && (
        <GoogleTagManager containerId={settings.gtm.container_id} />
      )}
      {settings.adsense?.enabled && settings.adsense?.publisher_id && (
        <GoogleAdSense
          publisherId={settings.adsense.publisher_id}
          slots={settings.adsense.slots || {}}
        />
      )}
      {settings.search_console?.enabled && settings.search_console?.verification_code && (
        <GoogleSearchConsole
          verificationCode={settings.search_console.verification_code}
        />
      )}
    </>
  );
};

export {
  GoogleAnalytics,
  GoogleTagManager,
  GoogleAdSense,
  GoogleSearchConsole,
};

export default GoogleServices;
