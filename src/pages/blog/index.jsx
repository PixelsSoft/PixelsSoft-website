import React from "react";
import DarkTheme from "../../layouts/Dark";
import Navbar from "../../components/Navbar";
import BlogStanderd from "../../components/Blog-standerd";
import Footer from "../../components/Footer";
import client from '../../config/sanity.config';

const BlogDark = ({posts = []}) => {
  const navbarRef = React.useRef(null);
  const logoRef = React.useRef(null);
  React.useEffect(() => {
    var navbar = navbarRef.current,
      logo = logoRef.current;
    if (window.pageYOffset > 300) {
      navbar.classList.add("nav-scroll");
    } else {
      navbar.classList.remove("nav-scroll");
    }
    window.addEventListener("scroll", () => {
      if (window.pageYOffset > 300) {
        navbar.classList.add("nav-scroll");
      } else {
        navbar.classList.remove("nav-scroll");
      }
    });
  }, [navbarRef]);
  return (
    <DarkTheme>
      <Navbar nr={navbarRef} lr={logoRef} />
      <section className="page-header">
        <div className="container">
          <div className="row">
            <div className="col-lg-7 col-md-9">
              <div className="cont">
                <h1 className="extra-title mb-10">Our Blog.</h1>
                <p>
                  All the most current news and events of our creative team.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>
      <BlogStanderd posts={posts} />
      <Footer />
    </DarkTheme>
  );
};


export async function getStaticProps() {
  let posts = await client.fetch('*[_type == "blog"]{title, excerpt, date, _id, _createdAt, slug, category, image{asset->{path,url}}} | order(_createdAt desc)')
  return {
    props: {
      posts
    }
  }
}



export default BlogDark;
