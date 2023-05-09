/* eslint-disable @next/next/no-img-element */
import React from "react";
import { Formik, Form, Field } from "formik";
import appData from "../../data/app.json";
import Image from "next/image";

const Footer = ( { noSubBG } ) => {
  function validateEmail( value ) {
    let error;
    if ( !value ) {
      error = "Required";
    } else if ( !/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i.test( value ) ) {
      error = "Invalid email address";
    }
    return error;
  }
  const sendEmail = ( ms ) => new Promise( ( r ) => setTimeout( r, ms ) );
  return (
    <footer className={`footer-half ${noSubBG ? '' : 'sub-bg'} section-padding pb-0`}>
      <div className="container">
        <div className="row">
          <div className="col-lg-5">
            <div className="cont">
              {/* <div className="logo"> */}
              {/* <a href="#0"> */}
              <Image src={`${appData.lightLogo}`} alt="" width={150} height={30} />
              {/* </a> */}
              {/* </div> */}
              <div className="con-info custom-font">
                <ul>
                  <li>
                    <span>Email : </span> <br /> Info@pixelssoft.com
                  </li>
                  <li>
                    <span>Address : </span>
                    <br />
                    <strong>USA:</strong> 4370 chicago drive sw 760 granville,USA
                    <br />
                    <strong>UK:</strong> 26a Somerton Road, Newport, United Kingdom NP19 8LD
                    <br />
                    <strong>CA:</strong> 3466 HALSTEAD RD MISSISSAUGA, ON, L5L 4G8 , Canada
                  </li>
                  <li>
                    <span>Phone : </span> <br />(+1) 386 306 6199, +44 7477 2275500
                  </li>
                </ul>
              </div>
              <div className="social-icon">
                <h6 className="custom-font stit simple-btn">Follow Us</h6>
                <div className="social">
                  <a href="https://www.facebook.com/profile.php?id=100064333501672" rel="noopener noreferrer" target={"_blank"} className="icon">
                    <i className="fab fa-facebook-f"></i>
                  </a>
                  <a href="https://www.instagram.com/pixelssoft/" target={"_blank"} rel="noopener noreferrer" className="icon">
                    <i className="fab fa-instagram"></i>
                  </a>
                  <a href="https://www.linkedin.com/company/pixelssoft/" target={"_blank"} rel="noopener noreferrer" className="icon">
                    <i className="fab fa-linkedin"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <div className="col-lg-5 offset-lg-2">
            <div className="subscribe mb-50">
              <h6 className="custom-font stit simple-btn">Newslatter</h6>
              <p>Sign up for subscribe out newsletter!</p>
              <Formik
                initialValues={{
                  subscribe: "",
                }}
                onSubmit={async ( values ) => {
                  await sendEmail( 500 );
                  alert( JSON.stringify( values, null, 2 ) );
                  // Reset the values
                  values.subscribe = "";
                }}
              >
                {( { errors, touched } ) => (
                  <Form>
                    <div className="form-group custom-font">
                      <Field
                        validate={validateEmail}
                        type="email"
                        name="subscribe"
                        placeholder="Your Email"
                      />
                      {errors.email && touched.email && (
                        <div>{errors.email}</div>
                      )}
                      <button className="cursor-pointer">Subscribe</button>
                    </div>
                  </Form>
                )}
              </Formik>
            </div>
            {/* <div className="insta">
              <h6 className="custom-font stit simple-btn">Instagram Post</h6>
              <div className="insta-gallary">
                <a href="#0">
                  <img src="/img/insta/1.jpg" alt="" />
                </a>
                <a href="#0">
                  <img src="/img/insta/2.jpg" alt="" />
                </a>
                <a href="#0">
                  <img src="/img/insta/3.jpg" alt="" />
                </a>
              </div>
            </div> */}
          </div>
        </div>
        <div className="copyrights text-center">
          <p>
            Copyright © 2022 PixelsSoft. All rights reserved
          </p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
