/* eslint-disable @next/next/no-img-element */
import React from "react";
import Clients1Data from "../../data/sections/clients1.json";

const Clients1 = ({ theme, subBG }) => {
  var first = Clients1Data.slice(0, Clients1Data.length / 2);
  var second = Clients1Data.slice(4, Clients1Data.length);
  return (
    <section className={`clients section-padding ${subBG ? "sub-bg" : ""}`}>
      <div className="container">
        <div className="row">
          <div className="col-lg-4 valign">
            <div className="sec-head custom-font mb-0">
              <h6>Clients</h6>
              <h3>
                Our <br /> Clients
              </h3>
            </div>
          </div>
          <div className="col-lg-8">
            <div>
              <div className="row bord">
                {first.map((item) => (
                  <div key={item.id} className="col-md-3 col-6 brands">
                    <div className="item">
                      <div className="img">
                        <img
                          width={132}
                          height={74}
                          src={
                            theme === "light" ? item.lightImage : item.darkImage
                          }
                          alt={`${item.url || "Client"} logo`}
                          loading="lazy"
                          decoding="async"
                        />
                      </div>
                    </div>
                  </div>
                ))}
              </div>
              <div className="row">
                {second.map((item) => (
                  <div
                    key={item.id}
                    className={`${
                      item.id == 5
                        ? "col-md-3 col-6 brands sm-mb30"
                        : item.id == 6
                        ? "col-md-3 col-6 brands sm-mb30"
                        : item.id == 7
                        ? "col-md-3 col-6 brands"
                        : item.id == 8
                        ? "col-md-3 col-6 brands"
                        : ""
                    }`}
                  >
                    <div className="item">
                      <div className="img">
                        <img
                          width={132}
                          height={74}
                          src={
                            theme === "light" ? item.lightImage : item.darkImage
                          }
                          alt={`${item.url || "Client"} logo`}
                          loading="lazy"
                          decoding="async"
                        />
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Clients1;
