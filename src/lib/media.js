export const getImageUrl = (image) =>
  image?.asset?.url || image?.url || "";

export const getSlugPath = (slug) =>
  (typeof slug === "object" ? slug?.current : slug) || "";
