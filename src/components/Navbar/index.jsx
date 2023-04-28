/* eslint-disable @next/next/no-img-element */
import React from "react";
// import { Formik, Form, Field } from "formik";
import Link from "next/link";
import Image from "next/image";
import appData from "../../data/app.json";
import {
  handleDropdown,
  handleMobileDropdown,
  // handleSearch,
} from "../../common/navbar";

const Navbar = ( { lr, nr, theme } ) => {
  React.useEffect( () => {
    // handleSearch();
  }, [] );
  return (
    <nav
      ref={nr}
      className={`navbar navbar-expand-lg change ${theme === "themeL" ? "light" : ""
        }`}
    >
      <div className="container">
        <Link href="/">
          {/* <a className="logo"> */}
        
              <Image src={`${appData.lightLogo}`} alt="logo" height={30} width={150}/>
            
          {/* </a> */}
        </Link>

        <button
          className="navbar-toggler"
          type="button"
          onClick={handleMobileDropdown}
          data-toggle="collapse"
          data-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span className="icon-bar">
            <i className="fas fa-bars"></i>
          </span>
        </button>

        <div className="collapse navbar-collapse" id="navbarSupportedContent">
          <ul className="navbar-nav ml-auto">

            <li className="nav-item">
              <Link href="/">
                <a className="nav-link">Home</a>
              </Link>
            </li>
            <li className="nav-item">
              <Link href="/showcase">
                <a className="nav-link">Show case</a>
              </Link>
            </li>

            <li className="nav-item">
              <Link href="/portfolio">
                <a className="nav-link">portfolio</a>
              </Link>
            </li>
            <li className="nav-item">
              <Link href="/about">
                <a className="nav-link">About</a>
              </Link>
            </li>
            {/* ============Blog section here============ */}
            <li className="nav-item">
              <Link href="/blog">
                <a className="nav-link">Blogs</a>
              </Link>
            </li>

            <li className="nav-item">
              <Link href="/contact">
                <a className="nav-link">Contact</a>
              </Link>
            </li>
          </ul>
          {/* <div className="search">
            <span className="icon pe-7s-search cursor-pointer"></span>
            <div className="search-form text-center custom-font">
              <Formik
                initialValues={{
                  search: "",
                }}
                onSubmit={async ( values ) => {
                  alert( JSON.stringify( values, null, 2 ) );
                  // Reset the values
                  values.search = "";
                }}
              >
                {( { errors, touched } ) => (
                  <Form>
                    <Field type="text" name="search" placeholder="Search" />
                  </Form>
                )}
              </Formik>
              <span className="close pe-7s-close cursor-pointer"></span>
            </div>
          </div> */}
        </div>
      </div>
    </nav>
  );
};

export default Navbar;
