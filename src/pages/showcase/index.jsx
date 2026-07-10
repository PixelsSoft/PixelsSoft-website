import React from "react";
import NavbarFullMenu from "../../components/Navbar-full-menu/navbar-full-menu";
import ShowcasesOneCenter from "../../components/Showcases-one-center";
import SEO from "../../components/SEO";
import DarkTheme from "../../layouts/Dark";
import client from "../../config/sanity.config";
import { fetchShowcases, normalizeShowcaseItems } from "../../lib/data";

const Showcase4Dark = ({showcaseItems: initialItems = []}) => {
  const [showcaseItems, setShowcaseItems] = React.useState(initialItems);

  React.useEffect(() => {
    fetchShowcases(initialItems).then((data) => {
      setShowcaseItems(normalizeShowcaseItems(data));
    });
  }, [initialItems]);

  return (
    <DarkTheme>
      <SEO
        title="Showcase"
        description="Browse the Pixels Soft showcase — a curated collection of our best creative and digital work."
        canonical="/showcase/"
      />
      <NavbarFullMenu />
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
