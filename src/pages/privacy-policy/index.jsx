import React from "react";
import Navbar from "../../components/Navbar";
import Footer from "../../components/Footer";
import SEO from "../../components/SEO";
import DarkTheme from "../../layouts/Dark";

const PrivacyPolicy = () => {
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
        title="Privacy Policy"
        description="Pixels Soft privacy policy — how we collect, use, and protect your data including cookies, analytics, and third-party services."
        canonical="/privacy-policy/"
      />
      <Navbar nr={navbarRef} />
      <section className="page-header">
        <div className="container">
          <div className="row">
            <div className="col-lg-8">
              <div className="cont">
                <h1 className="extra-title mb-10">Privacy Policy</h1>
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
                <h3>Information We Collect</h3>
                <p>
                  When you contact us through our website, we collect your name,
                  email address, subject, and message. We may also collect
                  technical data such as IP address and browser type through
                  analytics tools.
                </p>
                <h3>Cookies and Tracking</h3>
                <p>
                  We use cookies and similar technologies provided by Google
                  Analytics, Google Tag Manager, Google AdSense, and Google
                  reCAPTCHA to analyze traffic, serve relevant ads, and protect
                  our contact forms from spam.
                </p>
                <h3>Third-Party Services</h3>
                <p>
                  We use Tawk.to for live chat support. Google services may
                  process data according to their own privacy policies. We
                  recommend reviewing Google&apos;s privacy policy at
                  policies.google.com.
                </p>
                <h3>Data Retention</h3>
                <p>
                  Contact form submissions are retained as long as necessary to
                  respond to your inquiry and maintain business records.
                </p>
                <h3>Your Rights</h3>
                <p>
                  You may request access to, correction of, or deletion of your
                  personal data by contacting us at Info@pixelssoft.com.
                </p>
                <h3>Contact</h3>
                <p>
                  For privacy-related questions, email Info@pixelssoft.com.
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

export default PrivacyPolicy;
