// resources/js/flash.js - Enhanced Flash Message System
class FlashMessageSystem {
    constructor() {
        this.messages = [];
        this.container = null;
        this.maxMessages = 5;
        this.initialized = false;
        this.init();
    }

    init() {
        // Wait for DOM to be ready
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        this.createContainer();
        this.initialized = true;

        // Process any queued messages
        if (
            window.queuedFlashMessages &&
            Array.isArray(window.queuedFlashMessages)
        ) {
            window.queuedFlashMessages.forEach((msg) => this.add(msg));
            window.queuedFlashMessages = [];
        }
    }

    createContainer() {
        // Check if container already exists
        this.container = document.querySelector(".flash-messages-container");

        if (!this.container) {
            // Create container if it doesn't exist
            this.container = document.createElement("div");
            this.container.className =
                "flash-messages-container fixed top-4 right-4 z-[9999] space-y-3 w-full max-w-sm pointer-events-none";
            document.body.appendChild(this.container);
        }
    }

    getIcon(type) {
        const icons = {
            success: `<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
            </svg>`,
            error: `<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" />
            </svg>`,
            warning: `<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
            </svg>`,
            info: `<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 01.67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 11-.671-1.34l.041-.022zM12 9a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
            </svg>`,
        };
        return icons[type] || icons.info;
    }

    getColors(type) {
        const colors = {
            success: {
                bg: "bg-emerald-50",
                border: "border-emerald-200",
                text: "text-emerald-800",
                progress: "bg-emerald-500",
                icon: "text-emerald-500",
            },
            error: {
                bg: "bg-red-50",
                border: "border-red-200",
                text: "text-red-800",
                progress: "bg-red-500",
                icon: "text-red-500",
            },
            warning: {
                bg: "bg-amber-50",
                border: "border-amber-200",
                text: "text-amber-800",
                progress: "bg-amber-500",
                icon: "text-amber-500",
            },
            info: {
                bg: "bg-blue-50",
                border: "border-blue-200",
                text: "text-blue-800",
                progress: "bg-blue-500",
                icon: "text-blue-500",
            },
        };
        return colors[type] || colors.info;
    }

    add({ message, type = "success", duration = 5000, description = "" }) {
        if (!this.initialized) {
            // Queue message if not initialized
            if (!window.queuedFlashMessages) {
                window.queuedFlashMessages = [];
            }
            window.queuedFlashMessages.push({
                message,
                type,
                duration,
                description,
            });
            return null;
        }

        if (!this.container) {
            console.error("Flash container not available");
            return null;
        }

        const id =
            "flash_" +
            Date.now() +
            "_" +
            Math.random().toString(36).substr(2, 9);
        const colors = this.getColors(type);

        // Create message element
        const messageEl = document.createElement("div");
        messageEl.id = id;
        messageEl.className = `flash-message ${colors.bg} ${colors.border} ${colors.text} border rounded-xl shadow-lg overflow-hidden pointer-events-auto transform transition-all duration-300 translate-x-full opacity-0`;
        messageEl.setAttribute("role", "alert");
        messageEl.setAttribute("aria-live", "assertive");

        messageEl.innerHTML = `
            <div class="flex items-start p-4">
                <div class="flex-shrink-0 ${colors.icon}">
                    ${this.getIcon(type)}
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-sm font-semibold break-words">${this.escapeHtml(message)}</p>
                    ${description ? `<p class="text-sm mt-1 opacity-80 break-words">${this.escapeHtml(description)}</p>` : ""}
                </div>
                <button type="button" class="flash-close ml-4 flex-shrink-0 opacity-60 hover:opacity-100 focus:outline-none transition-opacity" aria-label="Close notification">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            ${
                duration > 0
                    ? `
                <div class="h-1 w-full bg-gray-200/50">
                    <div class="flash-progress h-full transition-all duration-100 ease-linear ${colors.progress}" style="width: 100%"></div>
                </div>
            `
                    : ""
            }
        `;

        // Add to container
        this.container.appendChild(messageEl);

        // Animate in
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                messageEl.classList.remove("translate-x-full", "opacity-0");
                messageEl.classList.add("translate-x-0", "opacity-100");
            });
        });

        // Setup auto-dismiss if duration > 0
        let timer = null;
        let startTime = Date.now();
        let remainingTime = duration;
        let isPaused = false;
        const progressBar = messageEl.querySelector(".flash-progress");

        if (duration > 0) {
            const updateProgress = () => {
                if (isPaused) return;

                const elapsed = Date.now() - startTime;
                remainingTime = duration - elapsed;
                const progress = (remainingTime / duration) * 100;

                if (progressBar) {
                    progressBar.style.width = `${Math.max(0, progress)}%`;
                }

                if (remainingTime <= 0) {
                    this.remove(id);
                }
            };

            const startTimer = () => {
                if (timer) clearInterval(timer);
                startTime = Date.now();
                isPaused = false;
                timer = setInterval(updateProgress, 50);
            };

            // Pause on hover
            messageEl.addEventListener("mouseenter", () => {
                isPaused = true;
                if (timer) clearInterval(timer);
            });

            // Resume on mouse leave
            messageEl.addEventListener("mouseleave", () => {
                if (remainingTime > 0) {
                    duration = remainingTime;
                    startTimer();
                }
            });

            startTimer();
        }

        // Close button
        const closeBtn = messageEl.querySelector(".flash-close");
        if (closeBtn) {
            closeBtn.addEventListener("click", () => this.remove(id));
        }

        // Add to messages array
        const messageObj = { id, element: messageEl, timer };
        this.messages.unshift(messageObj);

        // Limit messages
        if (this.messages.length > this.maxMessages) {
            const oldMsg = this.messages.pop();
            this.remove(oldMsg.id);
        }

        return id;
    }

    remove(id) {
        const messageIndex = this.messages.findIndex((msg) => msg.id === id);
        if (messageIndex === -1) return;

        const message = this.messages[messageIndex];

        // Clear timer
        if (message.timer) {
            clearInterval(message.timer);
        }

        // Animate out
        if (message.element && message.element.parentNode) {
            message.element.classList.remove("translate-x-0", "opacity-100");
            message.element.classList.add("translate-x-full", "opacity-0");

            setTimeout(() => {
                if (message.element && message.element.parentNode) {
                    message.element.remove();
                }
            }, 300);
        }

        // Remove from array
        this.messages.splice(messageIndex, 1);
    }

    clear() {
        this.messages.forEach((msg) => this.remove(msg.id));
    }

    escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize flash system
const flashSystem = new FlashMessageSystem();
window.flashSystem = flashSystem;

// Global flash function
window.flash = function (
    message,
    type = "success",
    duration = 5000,
    description = "",
) {
    return flashSystem.add({ message, type, duration, description });
};

// Export for ES modules
if (typeof module !== "undefined" && module.exports) {
    module.exports = { FlashMessageSystem, flash: window.flash };
}
