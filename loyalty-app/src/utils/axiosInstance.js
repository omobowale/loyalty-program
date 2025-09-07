// src/api/api.js
import axios from "axios";
import { useMockAuth } from "../context/MockAuthContext";

const useApi = () => {
  const { user } = useMockAuth();

  const API = axios.create({
    baseURL: "http://localhost:8000/api",
    headers: {
      "X-Mock-User": user?.id || "",
    },
  });

  return API;
};

export default useApi;
