/* eslint-disable @next/next/no-img-element */
import React from "react";
import Link from "next/link";
import initIsotope from "../../common/initIsotope";
import { getImageUrl } from "../../lib/media";

const PortfolioCustomColumn = ({
  portfolioItems,
  column,
  filterPosition,
  hideFilter,
  hideSectionTitle,
}) => {
  const [pageLoaded, setPageLoaded] = React.useState(false);
  React.useEffect(() => {
    setPageLoaded(true);
  }, []);

  React.useEffect(() => {
    if (!pageLoaded) return;
    const timer = setTimeout(() => {
      initIsotope();
    }, 500);
    return () => clearTimeout(timer);
  }, [pageLoaded, portfolioItems]);

  return (
    <section className="portfolio section-padding pb-70">
      {!hideSectionTitle && (
        <div className="container">
          <div className="sec-head custom-font">
            <h6>Portfolio</h6>
            <h3>Our Works.</h3>
            <span className="tbg text-right">Portfolio</span>
          </div>
        </div>
      )}

      <div className={`${column === 3 ? "container-fluid" : "container"}`}>
        <div className="row">
          {!hideFilter && (
            <div
              className={`filtering ${
                filterPosition === "center"
                  ? "text-center"
                  : filterPosition === "left"
                  ? "text-left"
                  : "text-right"
              } col-12`}
            >
              <div className="filter">
                <span data-filter="*" className="active">
                  All
                </span>
                <span data-filter=".brand">Branding</span>
                <span data-filter=".web">Mobile App</span>
                <span data-filter=".graphic">Creative</span>
              </div>
            </div>
          )}

          <div className="gallery full-width">
            {portfolioItems.map((item, index) => {
              const imageUrl = getImageUrl(item.image);
              return (
                <div
                  key={item?._id || item?.id || item?.slug || index}
                  className={`${
                    column === 3
                      ? "col-lg-4 col-md-6"
                      : column === 2
                      ? "col-md-6"
                      : "col-12"
                  } items ${item?.filterCategory || ""} ${
                    item.id === 2 && column == 3
                      ? "lg-mr"
                      : item.id === 1 && column == 2
                      ? "lg-mr"
                      : ""
                  }`}
                >
                  <div className="item-img">
                    <Link href="/portfolio/">
                      <a className="imago animated">
                        {imageUrl ? (
                          <img
                            src={imageUrl}
                            alt={
                              item?.title || item?.name || "Portfolio project"
                            }
                            loading="lazy"
                            decoding="async"
                          />
                        ) : (
                          <div
                            className="portfolio-img-fallback"
                            aria-hidden="true"
                          />
                        )}
                        <div className="item-img-overlay"></div>
                      </a>
                    </Link>
                  </div>
                  <div className="cont">
                    <h6>{item?.title || item?.name}</h6>
                    <span>
                      {item?.tags?.map((tag, tagIndex) => (
                        <React.Fragment key={`${tag}-${tagIndex}`}>
                          {tag}
                          {tagIndex == item?.tags.length - 1 ? "" : ","}
                        </React.Fragment>
                      ))}
                    </span>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </div>
    </section>
  );
};

export default PortfolioCustomColumn;
