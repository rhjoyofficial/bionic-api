const API_BASE = "/api/v1";

function getToken() {
    return localStorage.getItem("auth_token");
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? "";
}

export async function apiRequest(url, method = "GET", data = null) {
    const headers = {
        "Content-Type":  "application/json",
        "Accept":        "application/json",
        "X-CSRF-TOKEN":  getCsrfToken(),
    };

    const token = getToken();
    if (token) {
        headers["Authorization"] = `Bearer ${token}`;
    }

    const options = { method, headers };
    if (data && method !== "GET") {
        options.body = JSON.stringify(data);
    }

    const response = await fetch(API_BASE + url, options);
    const result   = await response.json();

    if (!response.ok) {
        throw result;
    }

    return result;
}
