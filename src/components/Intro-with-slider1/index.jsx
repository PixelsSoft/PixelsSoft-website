import React from "react";
import introData from "../../data/sections/intro.json";
import { Swiper, SwiperSlide } from "swiper/react";
import Link from "next/link";
import SwiperCore, { Navigation, Pagination, Autoplay } from "swiper";

import "swiper/css";
import "swiper/css/pagination";
import "swiper/css/navigation";
import fadeWhenScroll from "../../common/fadeWhenScroll";
import removeSlashFromPagination from "../../common/removeSlashFromPagination";

SwiperCore.use([Navigation, Pagination, Autoplay]);

const slideBg = (slide) => {
  const src = slide.image || slide.imageWebp;
  return src ? `url(${src})` : undefined;
};

const IntroWithSlider1 = ({ sliderRef }) => {
  const [ready, setReady] = React.useState(false);
  const [activeIndex, setActiveIndex] = React.useState(0);
  const firstSlide = introData[0];

  const navigationPrevRef = React.useRef(null);
  const navigationNextRef = React.useRef(null);
  const paginationRef = React.useRef(null);

  React.useEffect(() => {
    fadeWhenScroll();
    setReady(true);
    const t = setTimeout(() => removeSlashFromPagination(), 50);
    return () => clearTimeout(t);
  }, []);

  return (
    <header
      ref={sliderRef}
      className="slider slider-prlx fixed-slider text-center"
    >
      <div className="swiper-container parallax-slider">
        {!ready ? (
          <div className="swiper-slide">
            <div
              className="bg-img valign"
              style={{ backgroundImage: slideBg(firstSlide) }}
              data-overlay-dark="6"
            >
              <div className="container">
                <div className="row justify-content-center">
                  <div className="col-lg-7 col-md-9">
                    <div className="caption center">
                      <h1 className="custom-font">
                        {typeof firstSlide.title === "object" ? (
                          <>
                            {firstSlide.title.first} <br />
                            {firstSlide.title.second}
                          </>
                        ) : (
                          firstSlide.title
                        )}
                      </h1>
                      {firstSlide?.content && <p>{firstSlide.content}</p>}
                      <Link href="/about/">
                        <a className="btn-curve btn-lit mt-30">
                          <span>Look More</span>
                        </a>
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        ) : (
          <Swiper
            speed={800}
            autoplay={{
              delay: 5000,
              disableOnInteraction: true,
            }}
            allowTouchMove
            navigation={{
              prevEl: navigationPrevRef.current,
              nextEl: navigationNextRef.current,
            }}
            pagination={{
              type: "fraction",
              clickable: true,
              el: paginationRef.current,
            }}
            onBeforeInit={(swiper) => {
              swiper.params.navigation.prevEl = navigationPrevRef.current;
              swiper.params.navigation.nextEl = navigationNextRef.current;
              swiper.params.pagination.el = paginationRef.current;
            }}
            onSlideChange={(swiper) => setActiveIndex(swiper.realIndex)}
            onSwiper={(swiper) => {
              setTimeout(() => {
                swiper.params.navigation.prevEl = navigationPrevRef.current;
                swiper.params.navigation.nextEl = navigationNextRef.current;
                swiper.params.pagination.el = paginationRef.current;

                swiper.navigation.destroy();
                swiper.navigation.init();
                swiper.navigation.update();

                swiper.pagination.destroy();
                swiper.pagination.init();
                swiper.pagination.update();
              });
            }}
            className="swiper-wrapper"
            slidesPerView={1}
          >
            {introData.map((slide, index) => (
              <SwiperSlide key={slide.id} className="swiper-slide">
                <div
                  className="bg-img valign"
                  style={{ backgroundImage: slideBg(slide) }}
                  data-overlay-dark="6"
                >
                  <div className="container">
                    <div className="row justify-content-center">
                      <div className="col-lg-7 col-md-9">
                        <div className="caption center">
                          <h1
                            className="custom-font"
                            aria-hidden={index !== activeIndex}
                          >
                            {typeof slide.title === "object" ? (
                              <>
                                {slide.title.first} <br />
                                {slide.title.second}
                              </>
                            ) : (
                              slide.title
                            )}
                          </h1>
                          {slide?.content && <p>{slide.content}</p>}
                          <Link href="/about/">
                            <a className="btn-curve btn-lit mt-30">
                              <span>Look More</span>
                            </a>
                          </Link>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </SwiperSlide>
            ))}
          </Swiper>
        )}

        <div className="setone setwo">
          <div
            ref={navigationNextRef}
            className="swiper-button-next swiper-nav-ctrl next-ctrl cursor-pointer"
          >
            <i className="fas fa-chevron-right"></i>
          </div>
          <div
            ref={navigationPrevRef}
            className="swiper-button-prev swiper-nav-ctrl prev-ctrl cursor-pointer"
          >
            <i className="fas fa-chevron-left"></i>
          </div>
        </div>
        <div
          ref={paginationRef}
          className="swiper-pagination top botm custom-font"
        ></div>

        <div className="social-icon">
          <a
            href="https://www.facebook.com/profile.php?id=100064333501672"
            rel="noopener noreferrer"
            target="_blank"
            className="icon"
          >
            <i className="fab fa-facebook-f"></i>
          </a>
          <a
            href="https://www.instagram.com/pixelssoft/"
            target="_blank"
            rel="noopener noreferrer"
            className="icon"
          >
            <i className="fab fa-instagram"></i>
          </a>
          <a
            href="https://www.linkedin.com/company/pixelssoft/"
            target="_blank"
            rel="noopener noreferrer"
            className="icon"
          >
            <i className="fab fa-linkedin"></i>
          </a>
        </div>
      </div>
    </header>
  );
};

export default IntroWithSlider1;
