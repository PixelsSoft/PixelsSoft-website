import React from "react";
import DarkTheme from "../../layouts/Dark";
import addParlx from "../../common/addParlx";
import Navbar from "../../components/Navbar";
import CallToAction from "../../components/Call-to-action";
import PortfolioTreeColumn from "../../components/Portfolio-custom-column";
import SEO from "../../components/SEO";
import client from '../../config/sanity.config';
import { fetchPortfolios, normalizePortfolioItems } from '../../lib/data';

const Works4Dark = ( { portfolioItems: initialItems = [] } ) => {
  const [portfolioItems, setPortfolioItems] = React.useState(initialItems);
  const fixedHeader = React.useRef( null );
  const MainContent = React.useRef( null );
  const navbarRef = React.useRef( null );
  const logoRef = React.useRef( null );
  const [pageLoaded, setPageLoaded] = React.useState( false );

  React.useEffect(() => {
    fetchPortfolios(initialItems).then((data) => {
      setPortfolioItems(normalizePortfolioItems(data));
    });
  }, [initialItems]);

  React.useEffect( () => {
    setPageLoaded( true );
  }, [] );

  React.useEffect( () => {
    if ( !pageLoaded ) return;
    const updateLayout = () => {
      if ( !fixedHeader.current || !MainContent.current ) return;
      if ( window.innerWidth <= 991 ) {
        MainContent.current.style.marginTop = "0";
        return;
      }
      MainContent.current.style.marginTop =
        fixedHeader.current.offsetHeight + "px";
    };
    updateLayout();
    addParlx();
    window.addEventListener( "resize", updateLayout );
    return () => window.removeEventListener( "resize", updateLayout );
  }, [pageLoaded] );

  React.useEffect( () => {
    var navbar = navbarRef.current;
    const onScroll = () => {
      if ( window.pageYOffset > 300 ) {
        navbar.classList.add( "nav-scroll" );
      } else {
        navbar.classList.remove( "nav-scroll" );
      }
    };
    window.addEventListener( "scroll", onScroll );
    window.addEventListener( "load", () => {
      setTimeout( () => {
        if ( fixedHeader.current ) {
          var slidHeight = fixedHeader.current.offsetHeight;
          if ( MainContent.current ) {
            MainContent.current.style.marginTop = slidHeight + "px";
          }
        }
      }, 0 );
    } );
    return () => window.removeEventListener("scroll", onScroll);
  }, [fixedHeader, MainContent, navbarRef] );

  return (
    <DarkTheme>
      <SEO
        title="Portfolio"
        description="Explore Pixels Soft portfolio — web design, mobile apps, branding, and digital projects delivered for clients worldwide."
        canonical="/portfolio/"
      />
      <Navbar nr={navbarRef} lr={logoRef} />
      <header
        ref={fixedHeader}
        className="works-header fixed-slider hfixd valign"
      >
        <div className="container">
          <div className="row justify-content-center">
            <div className="col-lg-9 col-md-11 static">
              <div className="capt mt-50">
                <div className="parlx">
                  <h1 className="custom-font">My amazing works</h1>
                  <p>
                    Creative way to showcase your works at their absolute best.
                  </p>
                </div>
                <div className="bactxt custom-font valign">
                  <span className="full-width">Works</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>
      <div ref={MainContent} className="main-content">
        <PortfolioTreeColumn column={3} portfolioItems={portfolioItems} />
        <CallToAction />
        <footer className="footer-half sub-bg">
          <div className="container">
            <div className="copyrights text-center mt-0">
              <p>Copyright © {new Date().getFullYear()} PixelsSoft. All rights reserved</p>
            </div>
          </div>
        </footer>
      </div>
    </DarkTheme>
  );
};

export async function getStaticProps() {
  try {
    const query = `*[_type == "portfolio"]{_id, name, title, tags, image{asset->{path,url}}}`;
    const portfolioItems = await client.fetch(query);
    return { props: { portfolioItems } };
  } catch {
    return { props: { portfolioItems: [] } };
  }
}

export default Works4Dark;
