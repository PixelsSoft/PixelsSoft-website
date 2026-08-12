import {

  getBlogs,

  getAllBlogs,

  getBlog,

  getPortfolios,

  getShowcases,

  getServices,

  getGoogleSettings,

} from "./api";



const withFallback = async (fetcher, fallback) => {

  try {

    const data = await fetcher();

    if (Array.isArray(data) ? data.length > 0 : data && Object.keys(data).length > 0) {

      return data;

    }

    return fallback;

  } catch {

    return fallback;

  }

};



const resolveMediaUrl = (url) => {

  if (!url) return url;

  if (url.startsWith("http://") || url.startsWith("https://")) return url;

  const apiBase = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api/v1";

  const origin = apiBase.replace(/\/api\/v1\/?$/, "");

  return `${origin}${url.startsWith("/") ? url : `/${url}`}`;

};



const toImageAsset = (image) => {
  if (!image) return image;
  if (typeof image === "string") {
    const url = resolveMediaUrl(image);
    return url ? { asset: { url }, url } : image;
  }
  const url = resolveMediaUrl(image.url || image.asset?.url);
  return url ? { asset: { url }, url } : image;
};



const normalizeAuthor = (author) => {

  if (!author) return { name: "Pixels Soft", about: null };

  if (typeof author === "string") return { name: author, about: null };

  return {

    name: author.name || "Pixels Soft",

    about: author.about || null,

  };

};



export const BLOGS_PER_PAGE = 9;

export const fetchBlogs = (fallback = []) =>
  withFallback(async () => {
    const posts = await getAllBlogs();
    return normalizeBlogPosts(posts);
  }, fallback);

export const fetchBlogsPage = async (page = 1, perPage = BLOGS_PER_PAGE) => {
  try {
    const response = await getBlogs(page, perPage);
    return {
      posts: normalizeBlogPosts(response.data || []),
      meta: response.meta || {
        current_page: page,
        last_page: 1,
        per_page: perPage,
        total: (response.data || []).length,
      },
    };
  } catch {
    return {
      posts: [],
      meta: { current_page: page, last_page: 1, per_page: perPage, total: 0 },
    };
  }
};

export const fetchBlog = (slug, fallback = null) =>

  withFallback(() => getBlog(slug), fallback);

export const fetchPortfolios = (fallback = []) => withFallback(getPortfolios, fallback);

export const fetchShowcases = (fallback = []) => withFallback(getShowcases, fallback);

export const fetchServices = (fallback = []) => withFallback(getServices, fallback);

export const fetchGoogleSettings = (fallback = {}) =>

  withFallback(getGoogleSettings, fallback);



export const normalizePortfolioItems = (items = []) =>
  items.map((item) => {
    const rawCategory = item.filterCategory || item.category || "";
    const filterCategory = Array.isArray(rawCategory)
      ? rawCategory.filter(Boolean).join(" ")
      : String(rawCategory || "");

    return {
      ...item,
      _id: item._id || item.id,
      title: item.title || item.name,
      filterCategory,
      image: toImageAsset(item.image),
    };
  });



export const normalizeBlogPosts = (posts = []) =>

  posts.map((post) => ({

    ...post,

    _id: post._id || post.id,

    slug: post.slug?.current ? post.slug : { current: post.slug },

    author: normalizeAuthor(post.author),

    image: toImageAsset(post.image),

  }));



export const normalizeBlogPost = (post) => {

  if (!post) return post;

  return normalizeBlogPosts([post])[0];

};



export const normalizeShowcaseItems = (items = []) =>

  items.map((item) => ({

    ...item,

    _id: item.id || item._id,

    image: toImageAsset(item.image),

  }));

