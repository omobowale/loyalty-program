
import * as AdminService from "../services/admin"
import * as UserService from "../services/customers"
import useApi from "../utils/useApi";


export const useAchievementsApi = () => {
  const API = useApi();

  const getAllUsersAchievements = async () => {
    return AdminService.getAllUsersAchievements(API);
  };

  const getUserAchievements = async (userId) => {
    return UserService.getUserAchievements(API, userId)
  };

  return { getAllUsersAchievements, getUserAchievements };
};
