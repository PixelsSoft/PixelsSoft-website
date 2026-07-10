import React from "react";
import Navbar from "../../components/Navbar";
import Footer from "../../components/Footer";
import SEO from "../../components/SEO";
import DarkTheme from "../../layouts/Dark";

const Terms = () => {
  const navbarRef = React.useRef(null);

  React.useEffect(() => {
    const navbar = navbarRef.current;
    const onScroll = () => {
      if (window.pageYOffset > 300) {
        navbar.classList.add("nav-scroll");
      } else {
        navbar.classList.remove("nav-scroll");
      }
    };
    window.addEventListener("scroll", onScroll);
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  return (
    <DarkTheme>
      <SEO
        title="Terms of Service"
        description="Pixels Soft terms of service — rules and conditions for using our website and services."
        canonical="/terms/"
      />
      <Navbar nr={navbarRef} />
      <section className="page-header">
        <div className="container">
          <div className="row">
            <div className="col-lg-8">
              <div className="cont">
                <h1 className="extra-title mb-10">Terms of Service</h1>
                <p>Last updated: July 9, 2026</p>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section className="section-padding pt-0">
        <div className="container">
          <div className="row justify-content-center">
            <div className="col-lg-8">
              <div className="cont">
                <h3>Acceptance of Terms</h3>
                <p>
                  By accessing and using the Pixels Soft website, you agree to
                  these Terms of Service. If you do not agree, please do not use
                  our website.
                </p>
                <h3>Use of Website</h3>
                <p>
                  You may use this website for lawful purposes only. You may not
                  attempt to gain unauthorized access to our systems or use the
                  site to distribute harmful content.
                </p>
                <h3>Intellectual Property</h3>
                <p>
                  All content on this website, including text, images, logos,
                  and design, is owned by Pixels Soft unless otherwise stated.
                  You may not reproduce content without written permission.
                </p>
                <h3>Services</h3>
                <p>
                  Information on this website about our services is for general
                  informational purposes. Specific project terms are agreed upon
                  separately in writing.
                </p>
                <h3>Limitation of Liability</h3>
                <p>
                  Pixels Soft is not liable for any indirect or consequential
                  damages arising from use of this website.
                </p>
                <h3>Contact</h3>
                <p>
                  Questions about these terms? Email Info@pixelssoft.com.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>
      <Footer />
    </DarkTheme>
  );
};

export default Terms;
