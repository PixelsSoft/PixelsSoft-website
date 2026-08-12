/* eslint-disable @next/next/no-img-element */
import React from "react";
import AboutUs1Date from "../../data/sections/about-us1.json";

const AboutUs1 = () => {
  return (
    <div className="about section-padding">
      <div className="container">
        <div className="row">
          <div className="col-lg-5">
            <div className="img-mons">
              <div className="row">
                <div className="col-md-5 cmd-padding valign">
                  <div className="img1">
                    <img
                      src={AboutUs1Date.image1}
                      alt="Pixels Soft team"
                      width={500}
                      height={500}
                      loading="lazy"
                      decoding="async"
                    />
                  </div>
                </div>
                <div className="col-md-7 cmd-padding">
                  <div className="img2">
                    <img
                      src={AboutUs1Date.image2}
                      alt="Pixels Soft studio"
                      width={500}
                      height={500}
                      loading="lazy"
                      decoding="async"
                    />
                  </div>
                  <div className="img3">
                    <img
                      src={AboutUs1Date.image3}
                      alt="Pixels Soft work"
                      width={500}
                      height={500}
                      loading="lazy"
                      decoding="async"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div className="col-lg-6 offset-lg-1 valign">
            <div className="content">
              <div className="sub-title">
                <h6>{AboutUs1Date.smallTitle}</h6>
                <span></span>
                <span></span>
                <span></span>
              </div>
              <h3 className="main-title">
                {AboutUs1Date.title.first} <br /> {AboutUs1Date.title.second}
              </h3>
              <p className="txt">{AboutUs1Date.content}</p>
              <div className="ftbox mt-30">
                <ul>
                  {AboutUs1Date.features.map((feature) => (
                    <li
                      key={feature.id}
                      className={feature.id == 2 ? "space" : ""}
                    >
                      <span
                        className={`icon color-font pe-7s-${feature.icon}`}
                      ></span>
                      <h6 className="custom-font">
                        {feature.name.first} <br /> {feature.name.second}
                      </h6>
                      <div className="dots">
                        <span></span>
                        <span></span>
                        <span></span>
                      </div>
                    </li>
                  ))}
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AboutUs1;
