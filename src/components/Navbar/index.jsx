/* eslint-disable @next/next/no-img-element */
import React from "react";
import Link from "next/link";
import { useRouter } from "next/router";
import appData from "../../data/app.json";

const NAV_LINKS = [
  { href: "/", label: "Home" },
  { href: "/showcase", label: "Showcase" },
  { href: "/portfolio", label: "Portfolio" },
  { href: "/about", label: "About" },
  { href: "/blog", label: "Blogs" },
  { href: "/contact", label: "Contact" },
];

const Navbar = ({ nr, theme }) => {
  const router = useRouter();
  const [menuOpen, setMenuOpen] = React.useState(false);

  const closeMenu = React.useCallback(() => setMenuOpen(false), []);
  const toggleMenu = () => setMenuOpen((open) => !open);

  React.useEffect(() => {
    if (!menuOpen) return undefined;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";

    const onKeyDown = (event) => {
      if (event.key === "Escape") closeMenu();
    };

    window.addEventListener("keydown", onKeyDown);
    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", onKeyDown);
    };
  }, [menuOpen, closeMenu]);

  React.useEffect(() => {
    const handleRoute = () => closeMenu();
    router.events.on("routeChangeStart", handleRoute);
    return () => router.events.off("routeChangeStart", handleRoute);
  }, [router.events, closeMenu]);

  const isActive = (href) => {
    if (href === "/") return router.pathname === "/";
    return router.pathname === href || router.pathname.startsWith(`${href}/`);
  };

  return (
    <>
      <nav
        ref={nr}
        className={`navbar navbar-expand-lg change ps-navbar ${
          theme === "themeL" ? "light" : ""
        } ${menuOpen ? "menu-is-open" : ""}`}
      >
        <div className="container">
          <Link href="/">
            <a className="logo" onClick={closeMenu}>
              <img
                src={appData.lightLogo}
                alt="Pixels Soft logo"
                width={140}
                height={32}
                decoding="async"
              />
            </a>
          </Link>

          <ul className="navbar-nav ml-auto ps-desktop-nav">
            {NAV_LINKS.map((item) => (
              <li className="nav-item" key={item.href}>
                <Link href={item.href}>
                  <a
                    className={`nav-link ${
                      isActive(item.href) ? "active" : ""
                    }`}
                  >
                    {item.label}
                  </a>
                </Link>
              </li>
            ))}
          </ul>

          <button
            className={`ps-menu-toggle ${menuOpen ? "is-open" : ""}`}
            type="button"
            aria-label={menuOpen ? "Close menu" : "Open menu"}
            aria-expanded={menuOpen}
            aria-controls="ps-mobile-menu"
            onClick={toggleMenu}
          >
            <span className="ps-menu-toggle__bars" aria-hidden="true">
              <i />
              <i />
              <i />
            </span>
            <span className="ps-menu-toggle__label">
              {menuOpen ? "Close" : "Menu"}
            </span>
          </button>
        </div>
      </nav>

      <div
        id="ps-mobile-menu"
        className={`ps-mobile-menu ${menuOpen ? "is-open" : ""}`}
        aria-hidden={!menuOpen}
      >
        <div className="ps-mobile-menu__glow" aria-hidden="true" />
        <div className="ps-mobile-menu__inner">
          <div className="ps-mobile-menu__links">
            <p className="ps-mobile-menu__eyebrow">Navigate</p>
            <ul>
              {NAV_LINKS.map((item, index) => (
                <li key={item.href} style={{ "--i": index }}>
                  <Link href={item.href}>
                    <a
                      className={isActive(item.href) ? "active" : ""}
                      onClick={closeMenu}
                    >
                      <span className="ps-mobile-menu__num">
                        {String(index + 1).padStart(2, "0")}
                      </span>
                      <span className="ps-mobile-menu__label">{item.label}</span>
                    </a>
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div className="ps-mobile-menu__footer">
            <div className="ps-mobile-menu__meta">
              <a href="mailto:Info@pixelssoft.com">Info@pixelssoft.com</a>
              <a href="tel:+14067977989">(+1) 406 797-7989</a>
            </div>
            <Link href="/contact">
              <a className="ps-mobile-menu__cta" onClick={closeMenu}>
                Start a project
              </a>
            </Link>
          </div>
        </div>
      </div>
    </>
  );
};

export default Navbar;
