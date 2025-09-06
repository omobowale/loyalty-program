import API from "../../utils/axiosInstance";

export const getUserAchievements = (userId) => API.get(`/users/${userId}/achievements`);
