import React from "react";
import Split from "../Split";

const AboutIntro = () => {
  return (
    <section className="intro-section section-padding pb-0">
      <div className="container">
        <div className="row">
          <div className="col-lg-3 col-md-4">
            <div className="htit sm-mb30">
              <h4>Who We Are ?</h4>
            </div>
          </div>
          <div className="col-lg-8 offset-lg-1 col-md-8">
            <div className="text">
              <Split>
                <p
                  className="wow txt words chars splitting"
                  data-splitting
                  style={{ textAlign: "center" }}>
                  PixelsSoft is a dynamic and innovative IT company. Our team is made up of experienced professionals who are passionate about technology and are committed to providing our clients with the best possible solutions.
                  <br />
                  We believe that by staying true to our mission, we can help our clients achieve their goals and grow their businesses.
                  <br />

                  At PixelsSoft, we are dedicated to staying at the forefront of technology. We are constantly exploring new ways to use technology to help our clients succeed, and we are committed to ongoing learning and professional development.
                  <br />

                  We sare proud of what we will accomplished at PixelsSoft, and we are excited about the future. We look forward to continuing to work with our clients and to helping them achieve their goals.
                </p>
              </Split>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default AboutIntro;
