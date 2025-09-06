import API from "../../utils/axiosInstance";

export const getAllUsersAchievements = () => API.get('/admin/users/achievements');
