export const getImageUrl = (image) => {
  if (!image) return "";
  if (typeof image === "string") return image;
  return image?.asset?.url || image?.url || "";
};

export const getSlugPath = (slug) =>
  (typeof slug === "object" ? slug?.current : slug) || "";
