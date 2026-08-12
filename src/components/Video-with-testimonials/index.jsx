/* eslint-disable @next/next/no-img-element */
import React from "react";
import Slider from "react-slick";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";
import "react-modal-video/css/modal-video.css";

const VideoWithTestimonials = () => {
  const settings = {
    dots: true,
    infinite: true,
    arrows: false,
    speed: 500,
    slidesToShow: 1,
    slidesToScroll: 1,
  };
  return (
    <section className="block-sec">
      <div
        className="background bg-img section-padding pb-0"
        style={{ backgroundImage: `url(/img/slid/1.jpg)` }}
        data-overlay-dark="8"
      >
        <div className="container">
          <div className="row">
            <div className="col-lg-6">
              <div className="vid-area">
                <div className="cont">
                  <h3>
                    So that&apos;s us. There&apos;s no other way to put it.
                  </h3>
                </div>
              </div>
            </div>
            <div className="col-lg-5 offset-lg-1">
              <div className="testim-box">
                <div className="head-box">
                  <h6>Our Happy Clients</h6>
                  <h4>What Clients Say?</h4>
                </div>
                <Slider {...settings} className="slic-item">
                  <div className="item">
                    <p>
                      Will recommend him to everyone. It Was a great experience
                      working with him.
                    </p>
                    <div className="info">
                      <div className="cont">
                        <div className="author">
                          <h6 className="author-name custom-font">
                            Nadeem Khan
                          </h6>
                          <span className="author-details">
                            CEO , Arham Associates
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div className="item">
                    <p>
                      Very easy to work with and wrote beautiful code very
                      quickly. Great communication throughout. I would
                      absolutely work with her again.
                    </p>
                    <div className="info">
                      <div className="cont">
                        <div className="author">
                          <h6 className="author-name custom-font">Waqas</h6>
                          <span className="author-details">Director, Jips</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div className="item">
                    <p>
                      Great work. Very flexible. Open to making adjustments and
                      edits. Very friendly. Fast worker, and attentive to the
                      project. Job well done!
                    </p>
                    <div className="info">
                      <div className="cont">
                        <div className="author">
                          <h6 className="author-name custom-font">Shahid</h6>
                          <span className="author-details">
                            Co-founder, Construction Company
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </Slider>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default VideoWithTestimonials;
