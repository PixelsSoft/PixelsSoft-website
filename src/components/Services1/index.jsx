import React from "react";
import Link from "next/link";
import { fetchServices } from "../../lib/data";

const defaultServices = [
  {
    title: "Graphic Design ,  Web & Mobile Design",
    description:
      "We create a visuals that will help you stand out, grab people attention, and shine in your own unique way in a market that is already incredibly competitive.",
    icon: "pe-7s-paint-bucket",
  },
  {
    title: "Web & Mobile Developmet",
    description:
      "We are expert in developing excellent web & mobile app development solutions.",
    icon: "pe-7s-phone",
  },
  {
    title: "Social media Marketing",
    description:
      "Our team of SEO professionals are always abreast with the trends and updates being released by search engines.",
    icon: "pe-7s-display1",
  },
];

const Services1 = () => {
  const [services, setServices] = React.useState(defaultServices);

  React.useEffect(() => {
    fetchServices(defaultServices).then(setServices);
  }, []);

  return (
    <section className="services">
      <div className="container">
        <div className="sec-head custom-font text-center">
          <h6>Best Features</h6>
          <h3>Services.</h3>
          <span className="tbg">Services</span>
        </div>
        <div className="row">
          <div
            className="col-lg-3 col-md-6 item-box bg-img"
            style={{ backgroundImage: "url(/img/1.jpg)" }}
          >
            <h4 className="custom-font">
              Best Of <br /> Our Features
            </h4>
            <Link href="/about/">
              <a className="btn-curve btn-bord btn-lit mt-40">
                <span>See All Services</span>
              </a>
            </Link>
          </div>
          {services.map((service, index) => (
            <div
              key={service.id || index}
              className="col-lg-3 col-md-6 item-box"
            >
              <span className={`icon ${service.icon || "pe-7s-star"}`}></span>
              <h6 dangerouslySetInnerHTML={{ __html: service.title }} />
              <p>{service.description}</p>
            </div>
          ))}
        </div>
      </div>
      <div className="half-bg bottom"></div>
    </section>
  );
};

export default Services1;
