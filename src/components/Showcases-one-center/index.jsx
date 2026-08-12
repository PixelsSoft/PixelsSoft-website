/* eslint-disable @next/next/no-img-element */
import React from "react";
import Link from "next/link";
import { Swiper, SwiperSlide } from "swiper/react";
import SwiperCore, { Navigation, Autoplay } from "swiper";

import "swiper/css";
import "swiper/css/navigation";
import removeSlashFromPagination from "../../common/removeSlashFromPagination";
import { getImageUrl } from "../../lib/media";

SwiperCore.use([Navigation, Autoplay]);

const getTitleParts = (title) => {
  if (typeof title === "object" && title !== null) {
    return { first: title.first || "", second: title.second || "" };
  }
  if (typeof title === "string") {
    const parts = title.trim().split(/\s+/);
    return { first: parts[0] || title, second: parts.slice(1).join(" ") };
  }
  return { first: "Showcase", second: "Project" };
};

const getTitleLabel = (title) => {
  const { first, second } = getTitleParts(title);
  return [first, second].filter(Boolean).join(" ");
};

const ShowcaseTitle = ({ title, isPrimary, simple }) => {
  const { first, second } = getTitleParts(title);
  const Tag = isPrimary ? "h1" : "p";

  if (simple) {
    return (
      <Tag className="showcase-title-simple" aria-hidden={!isPrimary}>
        <Link href="/portfolio/">
          <a>{getTitleLabel(title)}</a>
        </Link>
      </Tag>
    );
  }

  return (
    <Tag
      className={isPrimary ? undefined : "showcase-slide-title"}
      aria-hidden={!isPrimary}
    >
      <Link href="/portfolio/">
        <a>
          <div className="stroke">{first}</div>
          {second ? <span>{second}</span> : null}
        </a>
      </Link>
    </Tag>
  );
};

const ShowcasesOneCenter = ({ showcaseItems = [] }) => {
  const [load, setLoad] = React.useState(true);
  const [activeIndex, setActiveIndex] = React.useState(0);
  const [isMobile, setIsMobile] = React.useState(false);

  const navigationPrevRef = React.useRef(null);
  const navigationNextRef = React.useRef(null);

  React.useEffect(() => {
    const mq = window.matchMedia("(max-width: 991px)");
    const sync = () => setIsMobile(mq.matches);
    sync();
    mq.addEventListener?.("change", sync);
    const t = setTimeout(() => {
      setLoad(false);
      removeSlashFromPagination();
    }, 0);
    return () => {
      mq.removeEventListener?.("change", sync);
      clearTimeout(t);
    };
  }, []);

  if (!showcaseItems.length) {
    return (
      <header className="slider showcase-carus">
        <div className="container text-center" style={{ padding: "30vh 16px" }}>
          <h2>Showcase coming soon</h2>
        </div>
      </header>
    );
  }

  return (
    <header className="slider showcase-carus">
      <div id="content-carousel-container-unq-1" className="swiper-container">
        {!load ? (
          <Swiper
            speed={800}
            centeredSlides={!isMobile}
            autoplay={{ delay: 4500, disableOnInteraction: false }}
            loop={showcaseItems.length > 1}
            spaceBetween={isMobile ? 12 : 30}
            slidesPerView={1}
            navigation={{
              prevEl: navigationPrevRef.current,
              nextEl: navigationNextRef.current,
            }}
            breakpoints={{
              0: { slidesPerView: 1, spaceBetween: 12, centeredSlides: false },
              768: { slidesPerView: 2, spaceBetween: 30, centeredSlides: true },
              1024: {
                slidesPerView: 2,
                spaceBetween: 200,
                centeredSlides: true,
              },
            }}
            onBeforeInit={(swiper) => {
              swiper.params.navigation.prevEl = navigationPrevRef.current;
              swiper.params.navigation.nextEl = navigationNextRef.current;
            }}
            onSlideChange={(swiper) => setActiveIndex(swiper.realIndex)}
            onSwiper={(swiper) => {
              setTimeout(() => {
                swiper.params.navigation.prevEl = navigationPrevRef.current;
                swiper.params.navigation.nextEl = navigationNextRef.current;
                swiper.navigation.destroy();
                swiper.navigation.init();
                swiper.navigation.update();
              });
            }}
            className="swiper-wrapper"
          >
            {showcaseItems.map((slide, index) => {
              const imageUrl = getImageUrl(slide.image);
              const label = getTitleLabel(slide.title);
              return (
                <SwiperSlide
                  key={slide._id || slide.id || index}
                  className="swiper-slide"
                >
                  <div
                    className="bg-img valign"
                    style={
                      imageUrl
                        ? { backgroundImage: `url(${imageUrl})` }
                        : undefined
                    }
                    data-overlay-dark={isMobile ? "5" : "1"}
                  >
                    {imageUrl ? (
                      <img
                        className="showcase-slide-media"
                        src={imageUrl}
                        alt={label || "Showcase project"}
                        loading={index === 0 ? "eager" : "lazy"}
                        decoding="async"
                      />
                    ) : null}
                    <div className="caption ontop">
                      <div className="o-hidden">
                        <ShowcaseTitle
                          title={slide.title}
                          isPrimary={index === activeIndex}
                          simple={isMobile}
                        />
                      </div>
                    </div>
                    {!isMobile ? (
                      <div className="copy-cap valign">
                        <div className="cap">
                          <ShowcaseTitle
                            title={slide.title}
                            isPrimary={false}
                          />
                        </div>
                      </div>
                    ) : null}
                  </div>
                </SwiperSlide>
              );
            })}
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
