import React from "react";
import ContactHeader from "../../components/Contact-header";
import ContactWithMap from "../../components/Contact-with-map";
import Navbar from "../../components/Navbar";
import SEO from "../../components/SEO";
import DarkTheme from "../../layouts/Dark";

const Contact = () => {
  const fixedHeader = React.useRef(null);
  const MainContent = React.useRef(null);
  const navbarRef = React.useRef(null);

  React.useEffect(() => {
    const updateLayout = () => {
      if (!fixedHeader.current || !MainContent.current) return;
      if (window.innerWidth <= 991) {
        MainContent.current.style.marginTop = "0";
        fixedHeader.current.style.position = "static";
        return;
      }
      fixedHeader.current.style.position = "";
      MainContent.current.style.marginTop =
        fixedHeader.current.offsetHeight + "px";
    };

    updateLayout();
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
    <DarkTheme>
      <SEO
        title="Contact"
        description="Get in touch with Pixels Soft for web design, mobile apps, and digital marketing projects. We would love to hear from you."
        canonical="/contact/"
      />
      <Navbar nr={navbarRef} />
      <ContactHeader sliderRef={fixedHeader} />
      <div className="main-content" ref={MainContent}>
        <ContactWithMap />
      </div>
    </DarkTheme>
  );
};

export default Contact;
