import Head from "next/head";

const SITE_NAME = "Pixels Soft";
const DEFAULT_DESCRIPTION =
  "Pixels Soft delivers creative web design, mobile apps, graphic design, and digital marketing solutions for businesses worldwide.";
const DEFAULT_OG_IMAGE = "/img/pixels-soft-logo.png";
const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL || "https://pixelssoft.com";

const SEO = ({
  title,
  description = DEFAULT_DESCRIPTION,
  canonical,
  ogImage = DEFAULT_OG_IMAGE,
  ogType = "website",
  noindex = false,
  jsonLd,
}) => {
  const pageTitle = title ? `${title} | ${SITE_NAME}` : SITE_NAME;
  const canonicalUrl = canonical
    ? `${SITE_URL}${canonical.startsWith("/") ? canonical : `/${canonical}`}`
    : SITE_URL;
  const ogImageUrl = ogImage && ogImage.startsWith("http")
    ? ogImage
    : `${SITE_URL}${ogImage || DEFAULT_OG_IMAGE}`;

  return (
    <Head>
      <title>{pageTitle}</title>
      <meta name="description" content={description} />
      {noindex && <meta name="robots" content="noindex, nofollow" />}
      <link rel="canonical" href={canonicalUrl} />

      <meta property="og:type" content={ogType} />
      <meta property="og:title" content={pageTitle} />
      <meta property="og:description" content={description} />
      <meta property="og:image" content={ogImageUrl} />
      <meta property="og:url" content={canonicalUrl} />
      <meta property="og:site_name" content={SITE_NAME} />

      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" content={pageTitle} />
      <meta name="twitter:description" content={description} />
      <meta name="twitter:image" content={ogImageUrl} />

      {jsonLd && (
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
        />
      )}
    </Head>
  );
};

export const organizationSchema = {
  "@context": "https://schema.org",
  "@type": "Organization",
  name: SITE_NAME,
  url: SITE_URL,
  logo: `${SITE_URL}/img/pixels-soft-logo.png`,
  email: "Info@pixelssoft.com",
  sameAs: [
    "https://www.facebook.com/profile.php?id=100064333501672",
    "https://www.instagram.com/pixelssoft/",
    "https://www.linkedin.com/company/pixelssoft/",
  ],
};

export const websiteSchema = {
  "@context": "https://schema.org",
  "@type": "WebSite",
  name: SITE_NAME,
  url: SITE_URL,
};

export default SEO;
