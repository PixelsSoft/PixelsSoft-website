import React from "react";
import Link from "next/link";

const COOKIE_KEY = "pixels_soft_cookie_consent";

const CookieConsent = ({ onAccept }) => {
  const [visible, setVisible] = React.useState(false);

  React.useEffect(() => {
    const consent = localStorage.getItem(COOKIE_KEY);
    if (!consent) setVisible(true);
  }, []);

  const accept = () => {
    localStorage.setItem(COOKIE_KEY, "accepted");
    setVisible(false);
    if (onAccept) onAccept();
  };

  if (!visible) return null;

  return (
    <div
      style={{
        position: "fixed",
        bottom: 0,
        left: 0,
        right: 0,
        zIndex: 9999,
        background: "#111",
        color: "#fff",
        padding: "16px 24px",
        display: "flex",
        flexWrap: "wrap",
        alignItems: "center",
        justifyContent: "space-between",
        gap: "12px",
        boxShadow: "0 -2px 12px rgba(0,0,0,0.3)",
      }}
    >
      <p style={{ margin: 0, flex: 1, fontSize: "14px", lineHeight: 1.5 }}>
        We use cookies and similar technologies for analytics, advertising, and
        chat support. See our{" "}
        <Link href="/privacy-policy/">
          <a style={{ color: "#75dab4", textDecoration: "underline" }}>
            Privacy Policy
          </a>
        </Link>
        .
      </p>
      <button
        onClick={accept}
        style={{
          background: "#75dab4",
          color: "#111",
          border: "none",
          padding: "10px 24px",
          cursor: "pointer",
          fontWeight: 600,
          borderRadius: "4px",
        }}
      >
        Accept
      </button>
    </div>
  );
};

export const hasCookieConsent = () => {
  if (typeof window === "undefined") return false;
  return localStorage.getItem(COOKIE_KEY) === "accepted";
};

export default CookieConsent;
