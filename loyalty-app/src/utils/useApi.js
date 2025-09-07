import axios from "axios";
import { useMockAuth } from "../context/MockAuthContext";

const useApi = () => {
  const { user } = useMockAuth();

  const API = axios.create({
    baseURL: import.meta.env.VITE_BACKEND_URL + "/api",
    headers: {
      "X-Mock-User": user?.id || "",
    },
  });

  return API;
};

export default useApi;
