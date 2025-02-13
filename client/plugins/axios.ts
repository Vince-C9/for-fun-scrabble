import axios from "axios";

export default defineNuxtPlugin((nuxtApp) => {
  const defaultUrl = "http://localhost:8001/api"; // Removed angle brackets

  const api = axios.create({
    baseURL: defaultUrl, // Corrected baseUrl to baseURL
    headers: {
      common: {},
    },
  });

  return {
    provide: {
      api,
    },
  };
});