/* eslint-disable @next/next/no-img-element */
import React from "react";
import { useRouter } from "next/router";
import appData from "../../data/app.json";

const MIN_VISIBLE_MS = 700;

/**
 * Simple brand loader — Pace.js removed.
 * Pace restarts on every XHR/fetch and trapped the site in a loading loop.
 */
const LoadingScreen = () => {
  const router = useRouter();
  const enabled = Boolean(appData.showLoading);
  const [visible, setVisible] = React.useState(enabled);
  const hideTimer = React.useRef(null);
  const shownAt = React.useRef(0);
  const pathRef = React.useRef("");

  const clearHideTimer = React.useCallback(() => {
    if (hideTimer.current) {
      window.clearTimeout(hideTimer.current);
      hideTimer.current = null;
    }
  }, []);

  const show = React.useCallback(() => {
    if (!enabled) return;
    clearHideTimer();
    shownAt.current = Date.now();
    setVisible(true);
    document.body?.classList.remove("hideX");
    document.querySelector("#preloader")?.classList.remove("isdone");
    document.querySelector(".loading-text")?.classList.remove("isdone");
  }, [clearHideTimer, enabled]);

  const hide = React.useCallback(() => {
    if (!enabled) return;
    clearHideTimer();
    const elapsed = Date.now() - (shownAt.current || Date.now());
    const wait = Math.max(0, MIN_VISIBLE_MS - elapsed);

    hideTimer.current = window.setTimeout(() => {
      document.querySelector("#preloader")?.classList.add("isdone");
      document.querySelector(".loading-text")?.classList.add("isdone");
      document.body?.classList.add("pace-done");
      hideTimer.current = window.setTimeout(() => setVisible(false), 700);
    }, wait);
  }, [clearHideTimer, enabled]);

  React.useEffect(() => {
    if (!enabled) {
      document.body?.classList.add("hideX");
      setVisible(false);
      return undefined;
    }

    pathRef.current = router.asPath;
    show();
    const readyTimer = window.setTimeout(hide, 450);

    const onRouteStart = (url) => {
      const nextPath = String(url || "").split("?")[0];
      const currentPath = String(pathRef.current || "").split("?")[0];
      if (nextPath === currentPath) return;
      show();
    };

    const onRouteDone = (url) => {
      if (url) pathRef.current = url;
      hide();
    };

    router.events.on("routeChangeStart", onRouteStart);
    router.events.on("routeChangeComplete", onRouteDone);
    router.events.on("routeChangeError", hide);

    return () => {
      window.clearTimeout(readyTimer);
      clearHideTimer();
      router.events.off("routeChangeStart", onRouteStart);
      router.events.off("routeChangeComplete", onRouteDone);
      router.events.off("routeChangeError", hide);
    };
    // Mount once — do not rebind on asPath (that re-shows the loader forever)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [enabled]);

  if (!enabled) return null;

  return (
    <div
      className={`ps-loader ${visible ? "is-visible" : "is-hidden"}`}
      aria-hidden={!visible}
      aria-busy={visible}
    >
      <div id="preloader" />
      <div className="loading-text ps-loader__brand">
        <img
          src={appData.lightLogo}
          alt="Pixels Soft"
          width={140}
          height={32}
          decoding="async"
        />
        <span>Loading</span>
      </div>
    </div>
  );
};

export default LoadingScreen;
