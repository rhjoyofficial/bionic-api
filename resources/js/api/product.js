import { apiRequest } from "./client.js";

export function getProducts() {
    return apiRequest("/products");
}

export function getProduct(slug) {
    return apiRequest(`/products/${slug}`);
}

export function searchProducts(query) {
    return apiRequest(`/products/search?q=${query}`);
}

export function getCategoryProducts(categoryId) {
    return apiRequest(`/products/search?category_id=${categoryId}`);
}

export function getRecommendations(productId) {
    return apiRequest(`/products/${productId}/recommendations`);
}
