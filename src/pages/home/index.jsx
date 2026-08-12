import React from "react";
import AboutUs1 from "../../components/About-us1";
import CallToAction from "../../components/Call-to-action";
import Clients1 from "../../components/Clients1";
import Footer from "../../components/Footer";
import IntroWithSlider1 from "../../components/Intro-with-slider1";
import Navbar from "../../components/Navbar";
import Numbers1 from "../../components/Numbers";
import Services1 from "../../components/Services1";
import SkillsCircle from "../../components/Skills-circle";
import VideoWithTestimonials from "../../components/Video-with-testimonials";
import Works1Slider from "../../components/Works1-slider";

const Homepage1 = () => {
  const fixedSlider = React.useRef(null);
  const MainContent = React.useRef(null);
  const navbarRef = React.useRef(null);
  const logoRef = React.useRef(null);

  React.useEffect(() => {
    const updateLayout = () => {
      if (!fixedSlider.current || !MainContent.current) return;
      if (window.innerWidth <= 991) {
        MainContent.current.style.marginTop = "0";
        fixedSlider.current.style.position = "static";
        return;
      }
      fixedSlider.current.style.position = "";
      MainContent.current.style.marginTop =
        fixedSlider.current.offsetHeight + "px";
    };

    updateLayout();
    // One delayed pass after hero/images settle — avoid 1s polling (forced reflows)
    const t = window.setTimeout(updateLayout, 300);
    window.addEventListener("resize", updateLayout);

    const navbar = navbarRef.current;
    const onScroll = () => {
      if (!navbar) return;
      if (window.pageYOffset > 300) {
        navbar.classList.add("nav-scroll");
      } else {
        navbar.classList.remove("nav-scroll");
      }
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => {
      window.clearTimeout(t);
      window.removeEventListener("resize", updateLayout);
      window.removeEventListener("scroll", onScroll);
    };
  }, []);

  return (
    <>
      <Navbar nr={navbarRef} lr={logoRef} />
      <IntroWithSlider1 sliderRef={fixedSlider} />
      <div ref={MainContent} className="main-content">
        <AboutUs1 />
        <Services1 />
        <Numbers1 />
        <Works1Slider />
        <VideoWithTestimonials />
        <SkillsCircle theme="dark" subBG />
        <Clients1 theme="dark" />
        <CallToAction subBG />
        <Footer />
      </div>
    </>
  );
};

export default Homepage1;
