/* eslint-disable @next/next/no-img-element */
import React from "react";

import initIsotope from "../../common/initIsotope";


const PortfolioTwoColumn = ( { portfolioItems } ) => {
  const [pageLoaded, setPageLoaded] = React.useState( false );
  React.useEffect( () => {
    setPageLoaded( true );
    if ( pageLoaded ) {
      setTimeout( () => {
        initIsotope();
      }, 1000 )
    }
  }, [pageLoaded] );
  return (
    <section className="portfolio section-padding pb-70">
      <div className="container">
        <div className="gallery full-width">

          {portfolioItems.map( item => (
            <div className="col-md-6 items web" key={item._id}>
              <div className="item-img wow fadeInUp" data-wow-delay=".4s">

                <a>
                  <img src={item.image.asset.url} alt="image" />
                </a>

                <div className="cont">
                  <h6>{item.name}</h6>
                  <span>
                    {
                      item.tags && item.tags.map( tag => tag ).join( ', ' )
                    }
                    {/* <a >Design</a>, <a >React js</a> */}
                  </span>
                </div>
              </div>
            </div>
          ) )}
          {/* <div className="col-md-6 items graphic">
            <div className="item-img wow fadeInUp" data-wow-delay=".4s">

              <a>
                <img src="/img/portfolio/full/design1.png" alt="image" />
              </a>

              <div className="cont">
                <h6>SMS Craft</h6>
                <span>
                  <a >Design</a>, <a >React js</a>
                </span>
              </div>
            </div>
          </div>

          <div className="col-md-6 items web">
            <div className="row">
              <div className="col-lg-10">
                <div className="item-img wow fadeInUp" data-wow-delay=".4s">
                  <a>
                    <img src="/img/portfolio/full/design2.png" alt="image" />
                  </a>
                  <div className="cont">
                    <h6>Digital Solution</h6>
                    <span>
                      <a >Design</a>, <a >WordPress</a>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="col-md-6 items brand">
            <div className="item-img wow fadeInUp" data-wow-delay=".4s">
              <a>
                <img src="/img/portfolio/full/design3.png" alt="image" />
              </a>

              <div className="cont">
                <h6>Chat ZNet</h6>
                <span>
                  <a >Mobile application</a>
                </span>
              </div>
            </div>
          </div>

          <div className="col-md-6 items brand">
            <div className="row">
              <div className="col-lg-10 offset-lg-2">
                <div className="item-img wow fadeInUp" data-wow-delay=".4s">

                  <a>
                    <img src="/img/portfolio/full/design4.png" alt="image" />
                  </a>

                  <div className="cont">
                    <h6>Food Delivery </h6>
                    <span>
                      <a>Mobile App</a>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="col-md-6 items web">
            <div className="row">
              <div className="col-lg-11 offset-lg-1">
                <div className="item-img wow fadeInUp" data-wow-delay=".4s">

                  <a>
                    <img src="/img/portfolio/full/design5.png" alt="image" />
                  </a>

                  <div className="cont">
                    <h6>Task Application</h6>
                    <span>
                      <a >Mobile application</a>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="col-md-6 items graphic">
            <div className="item-img wow fadeInUp" data-wow-delay=".4s">

              <a>
                <img src="/img/portfolio/full/design6.png" alt="image" />
              </a>

              <div className="cont">
                <h6>Hr Mangement system</h6>
                <span>
                  <a >Desktop Application</a>
                </span>
              </div>
            </div>
          </div>

          <div className="col-md-6 items brand">
            <div className="row">
              <div className="col-lg-10">
                <div className="item-img wow fadeInUp" data-wow-delay=".4s">

                  <a>
                    <img src="/img/portfolio/full/design7.png" alt="image" />
                  </a>

                  <div className="cont">
                    <h6>SAAS System</h6>
                    <span>
                      <a >Desktop Application</a>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div className="col-md-6 items web">
            <div className="item-img wow fadeInUp" data-wow-delay=".4s">

              <a>
                <img src="/img/portfolio/full/logo1.png" alt="logo" />
              </a>

              <div className="cont">
                <h6>Logo Work</h6>

              </div>
            </div>
          </div>
          <div className="col-md-6 items web">
            <div className="item-img wow fadeInUp" data-wow-delay=".4s">

              <a>
                <img src="/img/portfolio/full/logo2.png" alt="logo" />
              </a>

              <div className="cont">
                <h6>Logo Work</h6>

              </div>
            </div>
          </div>
          <div className="col-md-6 items web">
            <div className="item-img wow fadeInUp" data-wow-delay=".4s">

              <a>
                <img src="/img/portfolio/full/logo3.png" alt="logo" />
              </a>

              <div className="cont">
                <h6>Logo Work</h6>

              </div>
            </div>
          </div>
          <div className="col-md-6 items web">
            <div className="item-img wow fadeInUp" data-wow-delay=".4s">

              <a>
                <img src="/img/portfolio/full/logo4.png" alt="logo" />
              </a>

              <div className="cont">
                <h6>Logo Work</h6>

              </div>
            </div>
          </div>
          <div className="col-md-6 items web">
            <div className="item-img wow fadeInUp" data-wow-delay=".4s">

              <a>
                <img src="/img/portfolio/full/logo5.png" alt="logo" />
              </a>

              <div className="cont">
                <h6>Logo Work</h6>

              </div>
            </div>
          </div>
          <div className="col-md-6 items web">
            <div className="item-img wow fadeInUp" data-wow-delay=".4s">
              <a>
                <img src="/img/portfolio/full/logo6.png" alt="logo" />
              </a>
              <div className="cont">
                <h6>Logo Work</h6>

              </div>
            </div>
          </div>
          <div className="col-md-6 items web">
            <div className="item-img wow fadeInUp" data-wow-delay=".4s">
              <a>
                <img src="/img/portfolio/full/logo7.png" alt="logo" />
              </a>
              <div className="cont">
                <h6>Logo Work</h6>

              </div>
            </div>
          </div>
          <div className="col-md-6 items web">
            <div className="item-img wow fadeInUp" data-wow-delay=".4s">
              <a>
                <img src="/img/portfolio/full/logo8.png" alt="logo" />
              </a>
              <div className="cont">
                <h6>Logo Work</h6>

              </div>
            </div>
          </div>
          <div className="col-md-6 items web">
            <div className="item-img wow fadeInUp" data-wow-delay=".4s">
              <a>
                <img src="/img/portfolio/full/logo9.png" alt="logo" />
              </a>
              <div className="cont">
                <h6>Logo Work</h6>

              </div>
            </div>
          </div>
          <div className="col-md-6 items web">
            <div className="item-img wow fadeInUp" data-wow-delay=".4s">
              <a>
                <img src="/img/portfolio/full/menuCard.png" alt="logo" />
              </a>
              <div className="cont">
                <h6>Design Work</h6>

              </div>
            </div>
          </div> */}
        </div>
      </div>
    </section>
  );
};

export default PortfolioTwoColumn;
