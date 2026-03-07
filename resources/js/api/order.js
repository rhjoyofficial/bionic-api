import { apiRequest } from "./client.js";

export function checkout(data) {
    return apiRequest("/checkout", "POST", data);
}

export function getOrderTracking(orderId) {
    return apiRequest(`/orders/${orderId}/tracking`);
}
