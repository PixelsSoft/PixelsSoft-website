import React from "react";
import BlogDetails from "../../../components/Blog-details";
import Footer from "../../../components/Footer";
import Navbar from "../../../components/Navbar";
import SEO from "../../../components/SEO";
import { useRouter } from "next/router";
import { fetchBlog, normalizeBlogPost } from "../../../lib/data";
import { getBlog, getAllBlogs } from "../../../lib/api";
import DarkTheme from "../../../layouts/Dark";

const Post = ( { post: initialPost } ) => {
  const router = useRouter();
  const [post, setPost] = React.useState(initialPost);
  const navbarRef = React.useRef( null );
  const logoRef = React.useRef( null );

  React.useEffect( () => {
    document.querySelector( 'body' )?.classList.add( 'menubarblack' );
    if (router.query.post) {
      fetchBlog(router.query.post, initialPost).then((data) => {
        if (data) setPost(normalizeBlogPost(data));
      });
    }
  }, [router.query.post, initialPost] );

  React.useEffect( () => {
    var navbar = navbarRef.current;
    if (!navbar) return;
    const onScroll = () => {
      if ( window.pageYOffset > 300 ) {
        navbar.classList.add( "nav-scroll" );
      } else {
        navbar.classList.remove( "nav-scroll" );
      }
    };
    window.addEventListener( "scroll", onScroll );
    return () => window.removeEventListener( "scroll", onScroll );
  }, [] );

  if (!post) return null;

  const slug = typeof post.slug === 'object' ? post.slug?.current : post.slug;
  const imageUrl = post.image?.asset?.url || post.image?.url;

  return (
    <DarkTheme>
      <SEO
        title={post.title}
        description={post.excerpt || `Read ${post.title} on the Pixels Soft blog.`}
        canonical={`/blog/${slug}/`}
        ogType="article"
        ogImage={imageUrl}
        jsonLd={{
          "@context": "https://schema.org",
          "@type": "BlogPosting",
          headline: post.title,
          datePublished: post.date,
          author: {
            "@type": "Person",
            name: post.author?.name || post.author || "Pixels Soft",
          },
          image: imageUrl,
          publisher: {
            "@type": "Organization",
            name: "Pixels Soft",
          },
        }}
      />
      <Navbar nr={navbarRef} lr={logoRef} theme="themeL" />
      <section className="page-header blg">
        <div className="container">
          <div className="row justify-content-center">
            <div className="col-lg-7 col-md-9">
              <div className="cont text-center">
                <h1>{post.title}</h1>
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

export async function getStaticProps( context ) {
  const { params } = context;

  try {
    const raw = await getBlog(params.post);
    if (!raw) {
      return { notFound: true };
    }
    return { props: { post: normalizeBlogPost(raw) } };
  } catch {
    return { notFound: true };
  }
}

export async function getStaticPaths() {
  try {
    const posts = await getAllBlogs();
    const paths = (posts || [])
      .map((post) => {
        const slug = typeof post.slug === "object" ? post.slug?.current : post.slug;
        return slug ? { params: { post: slug } } : null;
      })
      .filter(Boolean);

    return { paths, fallback: false };
  } catch {
    return { paths: [], fallback: false };
  }
}

export default Post;
