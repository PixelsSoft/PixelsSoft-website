import Link from "next/link";
import { getImageUrl, getSlugPath } from "../../lib/media";

const FALLBACK_LOGO = "/img/pixels-soft-logo.png";

const formatDateLabel = (date) => {
  const months = [
    "Jan", "Feb", "Mar", "Apr", "May", "Jun",
    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
  ];
  const value = date ? new Date(date) : new Date();
  if (Number.isNaN(value.getTime())) {
    return "";
  }
  return `${months[value.getMonth()]} ${value.getDate()}, ${value.getFullYear()}`;
};

export default function BlogPostCard({ post, variant = "grid" }) {
  const { title, category, image, date, slug, excerpt } = post;
  const slugPath = getSlugPath(slug);
  const imageUrl = getImageUrl(image);

  if (variant === "list") {
    return (
      <div className="item mb-80">
        <div className="img">
          <Link href={"/blog/" + slugPath}>
            <a>
              {imageUrl ? (
                <img src={imageUrl} alt={title || "Blog post"} />
              ) : (
                <div className="blog-card-placeholder" style={{ minHeight: 280, background: "linear-gradient(145deg, #0c0e14, #1a1f2e)" }}>
                  <img src={FALLBACK_LOGO} alt="Pixels Soft" />
                </div>
              )}
            </a>
          </Link>
        </div>
        <div className="content">
          <div className="row">
            <div className="col-10">
              {category && (
                <div className="tags">
                  <a href="#0">{category}</a>
                </div>
              )}
              <h4 className="title">
                <Link href={"/blog/" + slugPath}>
                  <a>{title}</a>
                </Link>
              </h4>
              <p>{excerpt}</p>
              <Link href={"/blog/" + slugPath}>
                <a className="simple-btn mt-30">Read More</a>
              </Link>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <article className="blog-card-grid">
      <Link href={"/blog/" + slugPath}>
        <a className="blog-card-media">
          {imageUrl ? (
            <img src={imageUrl} alt={title || "Blog post"} loading="lazy" />
          ) : (
            <div className="blog-card-placeholder">
              <img src={FALLBACK_LOGO} alt="Pixels Soft" />
              <span>Pixels Soft Blog</span>
            </div>
          )}
        </a>
      </Link>
      <div className="blog-card-body">
        <div className="blog-card-meta">
          {formatDateLabel(date) && (
            <span className="blog-card-date">{formatDateLabel(date)}</span>
          )}
          {category && <span className="blog-card-category">{category}</span>}
        </div>
        <h3 className="blog-card-title">
          <Link href={"/blog/" + slugPath}>
            <a>{title}</a>
          </Link>
        </h3>
        {excerpt && <p className="blog-card-excerpt">{excerpt}</p>}
        <div className="blog-card-link">
          <Link href={"/blog/" + slugPath}>
            <a className="simple-btn">Read More</a>
          </Link>
        </div>
      </div>
    </article>
  );
}
