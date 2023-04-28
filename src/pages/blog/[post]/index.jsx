import React from "react";
import BlogDetails from "../../../components/Blog-details";
import Footer from "../../../components/Footer";
import Navbar from "../../../components/Navbar";
import LightTheme from "../../../layouts/Light";
import { useRouter } from "next/router";
import client from '../../../config/sanity.config'
import DarkTheme from "../../../layouts/Dark";

const Post = ({post}) => {
  const navbarRef = React.useRef(null);
  const logoRef = React.useRef(null);
  React.useEffect(() => {
    document.querySelector('body').classList.add('menubarblack');
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
      <Navbar nr={navbarRef} lr={logoRef} theme="themeL" />
      <section className="page-header blg">
        <div className="container">
          <div className="row justify-content-center">
            <div className="col-lg-7 col-md-9">
              <div className="cont text-center">
                <h2>{post.title}</h2>
              </div>
            </div>
          </div>
        </div>
      </section>
      <BlogDetails post={post} />
      <Footer />
    </DarkTheme>
  );
};


export async function getStaticProps(context) {
  const {params} = context
  console.log(params)

  const query = `*[_type == "blog" && slug.current == $slug] { _id, title, date, author -> {_id, name, about}, content, image{asset->{path,url}}, "slug":slug.current }`
  const options = {slug: params.post}

  const post = await client.fetch(query, options)

  console.log(post)

  return {
    props: {
      post: post[0]
    }
  }
}

export async function getStaticPaths() {
  const query = `*[_type == "blog"]{'slug': slug.current}`

  const posts = await client.fetch(query)

  const paths = posts.map(post => ({
    params: {
      post: post.slug
    }
  }))
  return {
    paths,
    fallback: false
  }
}


export default Post;
