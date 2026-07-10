/* eslint-disable @next/next/no-img-element */
import React from "react";
import BlogPostCard from "../Blog-post-card";
import { fetchBlogsPage, BLOGS_PER_PAGE } from "../../lib/data";

const BlogStanderd = () => {
  const [page, setPage] = React.useState(1);
  const [posts, setPosts] = React.useState([]);
  const [meta, setMeta] = React.useState({
    current_page: 1,
    last_page: 1,
    per_page: BLOGS_PER_PAGE,
    total: 0,
  });
  const [loading, setLoading] = React.useState(true);
  const sectionRef = React.useRef(null);

  React.useEffect(() => {
    let active = true;
    setLoading(true);

    fetchBlogsPage(page, BLOGS_PER_PAGE).then(({ posts: nextPosts, meta: nextMeta }) => {
      if (!active) return;
      setPosts(nextPosts);
      setMeta(nextMeta);
      setLoading(false);

      if (sectionRef.current) {
        const top = sectionRef.current.getBoundingClientRect().top + window.pageYOffset - 100;
        window.scrollTo({ top: Math.max(top, 0), behavior: "smooth" });
      }
    });

    return () => {
      active = false;
    };
  }, [page]);

  const goToPage = (nextPage) => {
    if (nextPage < 1 || nextPage > meta.last_page || nextPage === page) return;
    setPage(nextPage);
  };

  const pageNumbers = () => {
    const total = meta.last_page;
    const current = meta.current_page;
    const pages = [];

    if (total <= 7) {
      for (let i = 1; i <= total; i += 1) pages.push(i);
      return pages;
    }

    pages.push(1);
    if (current > 3) pages.push("ellipsis-start");
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i += 1) {
      pages.push(i);
    }
    if (current < total - 2) pages.push("ellipsis-end");
    pages.push(total);

    return pages;
  };

  const start = meta.total === 0 ? 0 : (meta.current_page - 1) * meta.per_page + 1;
  const end = Math.min(meta.current_page * meta.per_page, meta.total);

  return (
    <section className="blog-pg section-padding pt-0" ref={sectionRef}>
      <div className="container">
        <div className="row justify-content-center">
          <div className="col-lg-11">
            {meta.total > 0 && (
              <p className="blog-grid-summary">
                Showing {start}–{end} of {meta.total} articles
              </p>
            )}

            {loading ? (
              <div className="blog-grid-loading">Loading articles...</div>
            ) : posts.length === 0 ? (
              <div className="blog-grid-empty">No blog posts published yet.</div>
            ) : (
              <div className="posts blog-grid-layout">
                <div className="row">
                  {posts.map((post) => (
                    <div className="col-lg-4 col-md-6 mb-50" key={post._id}>
                      <BlogPostCard post={post} variant="grid" />
                    </div>
                  ))}
                </div>

                {meta.last_page > 1 && (
                  <div className="pagination">
                    <span className={page <= 1 ? "disabled" : ""}>
                      <a
                        href="#0"
                        onClick={(e) => {
                          e.preventDefault();
                          goToPage(page - 1);
                        }}
                        aria-label="Previous page"
                      >
                        <i className="fas fa-angle-left"></i>
                      </a>
                    </span>

                    {pageNumbers().map((item) =>
                      typeof item === "string" ? (
                        <span key={item} className="dots">
                          <a href="#0" onClick={(e) => e.preventDefault()}>...</a>
                        </span>
                      ) : (
                        <span key={item} className={item === page ? "active" : ""}>
                          <a
                            href="#0"
                            onClick={(e) => {
                              e.preventDefault();
                              goToPage(item);
                            }}
                          >
                            {item}
                          </a>
                        </span>
                      )
                    )}

                    <span className={page >= meta.last_page ? "disabled" : ""}>
                      <a
                        href="#0"
                        onClick={(e) => {
                          e.preventDefault();
                          goToPage(page + 1);
                        }}
                        aria-label="Next page"
                      >
                        <i className="fas fa-angle-right"></i>
                      </a>
                    </span>
                  </div>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    </section>
  );
};

export default BlogStanderd;
