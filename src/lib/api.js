import axios from "axios";

const API_BASE =
  process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api/v1";

const api = axios.create({
  baseURL: API_BASE,
  headers: { Accept: "application/json" },
});

export const getBlogs = async (page = 1, perPage = 9) => {
  const { data } = await api.get("/blogs", {
    params: { page, per_page: perPage },
  });
  return data;
};

export const getAllBlogs = async () => {
  const perPage = 24;
  let page = 1;
  let lastPage = 1;
  const posts = [];

  do {
    const response = await getBlogs(page, perPage);
    posts.push(...(response.data || []));
    lastPage = response.meta?.last_page || 1;
    page += 1;
  } while (page <= lastPage);

  return posts;
};

export const getBlog = async (slug) => {
  const { data } = await api.get(`/blogs/${slug}`);
  return data.data || data;
};

export const getPortfolios = async () => {
  const { data } = await api.get("/portfolios");
  return data.data || data;
};

export const getShowcases = async () => {
  const { data } = await api.get("/showcases");
  return data.data || data;
};

export const getServices = async () => {
  const { data } = await api.get("/services");
  return data.data || data;
};

export const getSections = async (page) => {
  const { data } = await api.get(`/sections/${page}`);
  return data.data || data;
};

export const getSettings = async () => {
  const { data } = await api.get("/settings");
  return data.data || data;
};

export const getGoogleSettings = async () => {
  const { data } = await api.get("/settings/google");
  return data.data || data;
};

export const submitContact = async (formData) => {
  const { data } = await api.post("/contact", formData);
  return data;
};

export default api;
