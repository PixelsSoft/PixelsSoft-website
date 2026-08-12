import React from "react";
import Navbar from "../../components/Navbar";
import ShowcasesOneCenter from "../../components/Showcases-one-center";
import SEO from "../../components/SEO";
import DarkTheme from "../../layouts/Dark";
import client from "../../config/sanity.config";
import { fetchShowcases, normalizeShowcaseItems } from "../../lib/data";
import { getImageUrl } from "../../lib/media";
import showcaseFallback from "../../data/showcases-full-screen-slider.json";

const Showcase4Dark = ({showcaseItems: initialItems = []}) => {
  const [showcaseItems, setShowcaseItems] = React.useState(() =>
    normalizeShowcaseItems(
      initialItems.length ? initialItems : showcaseFallback
    )
  );
  const navbarRef = React.useRef(null);

  React.useEffect(() => {
    const fallback = initialItems.length ? initialItems : showcaseFallback;
    fetchShowcases(fallback).then((data) => {
      const normalized = normalizeShowcaseItems(data);
      const withImages = normalized.filter((item) => getImageUrl(item.image));
      setShowcaseItems(
        withImages.length ? withImages : normalizeShowcaseItems(showcaseFallback)
      );
    });
  }, [initialItems]);

  React.useEffect(() => {
    const navbar = navbarRef.current;
    const onScroll = () => {
      if (!navbar) return;
      if (window.pageYOffset > 300) {
        navbar.classList.add("nav-scroll");
      } else {
        navbar.classList.remove("nav-scroll");
      }
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  return (
    <DarkTheme>
      <SEO
        title="Showcase"
        description="Browse the Pixels Soft showcase — a curated collection of our best creative and digital work."
        canonical="/showcase/"
      />
      <Navbar nr={navbarRef} />
      <ShowcasesOneCenter showcaseItems={showcaseItems} />
    </DarkTheme>
  );
};

export async function getStaticProps() {
  try {
    const query = `*[_type == "showcase"]{_id, title, image{asset->{path,url}}, sub}`;
    const showcaseItems = await client.fetch(query);
    return { props: { showcaseItems } };
  } catch {
    return { props: { showcaseItems: [] } };
  }
}

export default Showcase4Dark;
