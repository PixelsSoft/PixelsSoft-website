import React from "react";
import NavbarFullMenu from "../../components/Navbar-full-menu/navbar-full-menu";
import ShowcasesOneCenter from "../../components/Showcases-one-center";
import DarkTheme from "../../layouts/Dark";
import client from "../../config/sanity.config";

const Showcase4Dark = ({showcaseItems}) => {
  return (
    <DarkTheme>
      <NavbarFullMenu />
      <ShowcasesOneCenter showcaseItems={showcaseItems} />
    </DarkTheme>
  );
};

export async function getStaticProps() {

  const query = `*[_type == "showcase"]{_id, title, image{asset->{path,url}}, sub}`
  const showcaseItems = await client.fetch(query)

  return {
    props: {
      showcaseItems
    }
  }
}

export default Showcase4Dark;
