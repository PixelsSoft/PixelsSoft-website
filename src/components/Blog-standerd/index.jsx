/* eslint-disable @next/next/no-img-element */
import React from "react";
import Link from "next/link";
import BlogPostCard from "../Blog-post-card";

const BlogStanderd = ({posts}) => {
  return (
    <section className="blog-pg section-padding pt-0">
      <div className="container">
        <div className="row justify-content-center">
          <div className="col-lg-11">
            <div className="posts">
              {posts.map(post => (
                <BlogPostCard post={post} key={post._id} />
              ))}
              
              {/* <div className="pagination">
                <span className="active">
                  <a href="#0">1</a>
                </span>
                <span>
                  <a href="#0">2</a>
                </span>
                <span>
                  <a href="#0">
                    <i className="fas fa-angle-right"></i>
                  </a>
                </span>
              </div> */}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default BlogStanderd;
