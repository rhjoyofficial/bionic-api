const API_BASE = "/api";

function getToken() {
    return localStorage.getItem("auth_token");
}

export async function apiRequest(url, method = "GET", data = null) {
    const options = {
        method: method,
        headers: {
            "Content-Type": "application/json",
        },
    };

    const token = getToken();

    if (token) {
        options.headers["Authorization"] = `Bearer ${token}`;
    }

    if (data) {
        options.body = JSON.stringify(data);
    }

    const response = await fetch(API_BASE + url, options);

    const result = await response.json();

    if (!response.ok) {
        throw result;
    }

    return result;
}
