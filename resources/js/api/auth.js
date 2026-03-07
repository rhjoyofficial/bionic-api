import { apiRequest } from "./client.js";

export async function login(email, password) {
    const response = await apiRequest("/login", "POST", {
        email,
        password,
    });

    localStorage.setItem("auth_token", response.data.token);

    return response;
}

export async function register(data) {
    return await apiRequest("/register", "POST", data);
}

export async function logout() {
    await apiRequest("/logout", "POST");

    localStorage.removeItem("auth_token");
}
