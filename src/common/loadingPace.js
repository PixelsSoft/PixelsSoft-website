import { delay, animateEl } from "./customFunctions";

const loadingPace = () => {
  if (typeof window === "undefined" || typeof window.Pace === "undefined") {
    return;
  }

  const Pace = window.Pace;

  Pace.on("start", function () {
    document.querySelector("#preloader")?.classList.remove("isdone");
    document.querySelector(".loading-text")?.classList.remove("isdone");
  });

  Pace.on("done", function () {
    if (document.querySelector(".hamenu")) {
      delay(300, animateEl(document.querySelector(".hamenu"), "-100%"));
      document.querySelector(".topnav .menu-icon")?.classList.remove("open");
    }
    document.querySelector("#preloader")?.classList.add("isdone");
    document.querySelector(".loading-text")?.classList.add("isdone");
  });

  if (document.body?.classList.contains("pace-done")) {
    document.querySelector("#preloader")?.classList.add("isdone");
    document.querySelector(".loading-text")?.classList.add("isdone");
  }
};

export default loadingPace;
