import React from "react";
import DarkTheme from "../../layouts/Dark";
import addParlx from "../../common/addParlx";
import Navbar from "../../components/Navbar";
import CallToAction from "../../components/Call-to-action";
import PortfolioTwoColumn from "../../components/Portfolio-two-column";
import client from '../../config/sanity.config'

const Works4Dark = ({portfolioItems}) => {
  console.log(portfolioItems)
  const fixedHeader = React.useRef( null );
  const MainContent = React.useRef( null );
  const navbarRef = React.useRef( null );
  const logoRef = React.useRef( null );

  const [pageLoaded, setPageLoaded] = React.useState( false );
  React.useEffect( () => {
    setPageLoaded( true );
    if ( pageLoaded ) {
      addParlx();
    }
  }, [pageLoaded] );
  React.useEffect( () => {
    var navbar = navbarRef.current;
    if ( window.pageYOffset > 300 ) {
      navbar.classList.add( "nav-scroll" );
    } else {
      navbar.classList.remove( "nav-scroll" );
    }
    window.addEventListener( "scroll", () => {
      if ( window.pageYOffset > 300 ) {
        navbar.classList.add( "nav-scroll" );
      } else {
        navbar.classList.remove( "nav-scroll" );
      }
    } );
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
  }, [fixedHeader, MainContent, navbarRef] );

  return (
    <DarkTheme>
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
                  <h2 className="custom-font">My amazing works</h2>
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
        <PortfolioTwoColumn portfolioItems={portfolioItems} />
        <CallToAction />
        <footer className="footer-half sub-bg">
          <div className="container">
            <div className="copyrights text-center mt-0">
              <p>
                Copyright © 2022 PixelsSoft. All rights reserved
              </p>
            </div>
          </div>
        </footer>
      </div>
    </DarkTheme>
  );
};

export async function getStaticProps() {
  
  const query = `*[_type == "portfolio"]{_id, name, tags, image{asset->{path,url}}}`

  const portfolioItems = await client.fetch(query)
  
  return {
    props: {
      portfolioItems
    }
  }
}

export default Works4Dark;
