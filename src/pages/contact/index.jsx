import React from "react";
import ContactHeader from "../../components/Contact-header";
import ContactWithMap from "../../components/Contact-with-map";
import Navbar from "../../components/Navbar";
import SEO from "../../components/SEO";
import DarkTheme from "../../layouts/Dark";

const Contact = () => {
  const fixedHeader = React.useRef( null );
  const MainContent = React.useRef( null );
  const navbarRef = React.useRef( null );
  React.useEffect( () => {
    const interval = setInterval( () => {
      if ( fixedHeader.current && MainContent.current ) {
        MainContent.current.style.marginTop =
          fixedHeader.current.offsetHeight + "px";
      }
    }, 1000 );

    const navbar = navbarRef.current;
    const onScroll = () => {
      if (!navbar) return;
      if ( window.pageYOffset > 300 ) {
        navbar.classList.add( "nav-scroll" );
      } else {
        navbar.classList.remove( "nav-scroll" );
      }
    };
    window.addEventListener( "scroll", onScroll );
    return () => {
      clearInterval(interval);
      window.removeEventListener("scroll", onScroll);
    };
  }, [] );
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
