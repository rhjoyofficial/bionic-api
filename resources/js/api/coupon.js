import { apiRequest } from "./client.js";

export function validateCoupon(code, amount) {
    return apiRequest("/coupon/validate", "POST", {
        code: code,
        order_amount: amount,
    });
}
