import { apiRequest } from "./client.js";

export function getCart() {
    return apiRequest("/cart");
}

export function addToCart(variantId, quantity = 1) {
    return apiRequest("/cart/add", "POST", {
        variant_id: variantId,
        quantity: quantity,
    });
}

export function updateCart(variantId, quantity) {
    return apiRequest("/cart/update", "POST", {
        variant_id: variantId,
        quantity: quantity,
    });
}

export function removeFromCart(variantId) {
    return apiRequest("/cart/remove", "POST", {
        variant_id: variantId,
    });
}

export function clearCart() {
    return apiRequest("/cart/clear", "DELETE");
}
