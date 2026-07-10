import React from "react";
import Link from "next/link";
import { Swiper, SwiperSlide } from "swiper/react";
import SwiperCore, { Navigation, Parallax, Mousewheel } from "swiper";

import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/mousewheel";
import removeSlashFromPagination from "../../common/removeSlashFromPagination";
import { getImageUrl } from "../../lib/media";

SwiperCore.use([Navigation, Parallax, Mousewheel]);

const getTitleParts = (title) => {
  if (typeof title === "object" && title !== null) {
    return { first: title.first || "", second: title.second || "" };
  }
  if (typeof title === "string") {
    const parts = title.split(" ");
    return { first: parts[0] || title, second: parts.slice(1).join(" ") };
  }
  return { first: "Showcase", second: "Project" };
};

const ShowcaseTitle = ({ title, isPrimary }) => {
  const { first, second } = getTitleParts(title);
  const Tag = isPrimary ? "h1" : "p";

  return (
    <Tag className={isPrimary ? undefined : "showcase-slide-title"} aria-hidden={!isPrimary}>
      <Link href="/portfolio/">
        <a>
          <div className="stroke">{first}</div>
          <span>{second}</span>
        </a>
      </Link>
    </Tag>
  );
};

const ShowcasesOneCenter = ({ showcaseItems }) => {
  const [load, setLoad] = React.useState(true);
  const [activeIndex, setActiveIndex] = React.useState(0);

  React.useEffect(() => {
    setTimeout(() => {
      setLoad(false);
      removeSlashFromPagination();
    });
  }, []);

  const navigationPrevRef = React.useRef(null);
  const navigationNextRef = React.useRef(null);

  return (
    <header className="slider showcase-carus">
      <div id="content-carousel-container-unq-1" className="swiper-container">
        {!load ? (
          <Swiper
            speed={1000}
            mousewheel={true}
            centeredSlides={true}
            autoplay={true}
            loop={true}
            spaceBetween={30}
            navigation={{
              prevEl: navigationPrevRef.current,
              nextEl: navigationNextRef.current,
            }}
            breakpoints={{
              0: { slidesPerView: 1, spaceBetween: 0 },
              640: { slidesPerView: 1, spaceBetween: 0 },
              768: { slidesPerView: 2, spaceBetween: 30 },
              1024: { slidesPerView: 2, spaceBetween: 200 },
            }}
            onBeforeInit={(swiper) => {
              swiper.params.navigation.prevEl = navigationPrevRef.current;
              swiper.params.navigation.nextEl = navigationNextRef.current;
            }}
            onSlideChange={(swiper) => setActiveIndex(swiper.realIndex)}
            onSwiper={(swiper) => {
              setTimeout(() => {
                for (var i = 0; i < swiper.slides.length; i++) {
                  swiper.slides[i].childNodes[0].setAttribute(
                    "data-swiper-parallax",
                    0.75 * swiper.width
                  );
                }

                swiper.params.navigation.prevEl = navigationPrevRef.current;
                swiper.params.navigation.nextEl = navigationNextRef.current;

                swiper.navigation.destroy();
                swiper.navigation.init();
                swiper.navigation.update();
              });
            }}
            className="swiper-wrapper"
          >
            {showcaseItems.map((slide, index) => (
              <SwiperSlide key={slide._id || slide.id || index} className="swiper-slide">
                <div
                  className="bg-img valign"
                  style={{
                    backgroundImage: getImageUrl(slide.image)
                      ? `url(${getImageUrl(slide.image)})`
                      : undefined,
                  }}
                  data-overlay-dark="1"
                >
                  <div className="caption ontop">
                    <div className="o-hidden">
                      <ShowcaseTitle
                        title={slide.title}
                        isPrimary={index === activeIndex}
                      />
                    </div>
                  </div>
                  <div className="copy-cap valign">
                    <div className="cap">
                      <ShowcaseTitle
                        title={slide.title}
                        isPrimary={false}
                      />
                    </div>
                  </div>
                </div>
              </SwiperSlide>
            ))}
          </Swiper>
        ) : null}
        <div className="txt-botm">
          <div
            ref={navigationNextRef}
            className="swiper-button-next swiper-nav-ctrl next-ctrl cursor-pointer"
          >
            <div>
              <span>Next Slide</span>
            </div>
            <div>
              <i className="fas fa-chevron-right"></i>
            </div>
          </div>
          <div
            ref={navigationPrevRef}
            className="swiper-button-prev swiper-nav-ctrl prev-ctrl cursor-pointer"
          >
            <div>
              <i className="fas fa-chevron-left"></i>
            </div>
            <div>
              <span>Prev Slide</span>
            </div>
          </div>
        </div>
      </div>
    </header>
  );
};

export default ShowcasesOneCenter;
