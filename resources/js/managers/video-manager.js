class VideoManager {

    constructor(options = {}) {

        this.selector = options.selector || "[data-video]";
        this.autoPauseOthers = options.autoPauseOthers ?? true;
        this.pauseWhenOutOfView = options.pauseWhenOutOfView ?? true;

        this.instances = [];

        this.init();
    }

    init() {

        const containers = document.querySelectorAll(this.selector);

        containers.forEach(container => {
            const instance = this.createInstance(container);
            if (instance) {
                this.instances.push(instance);
            }
        });

        if (this.pauseWhenOutOfView) {
            this.initIntersectionObserver();
        }
    }

    createInstance(container) {

        const video = container.querySelector("video");

        if (!video) return null;

        const instance = {
            container,
            video,
            playBtn: container.querySelector("[data-video-play]"),
            pauseBtn: container.querySelector("[data-video-pause]"),
            toggleBtn: container.querySelector("[data-video-toggle]"),
            progress: container.querySelector("[data-video-progress]")
        };

        this.attachEvents(instance);

        return instance;
    }

    attachEvents(instance) {

        const { video, playBtn, pauseBtn, toggleBtn, progress } = instance;

        if (playBtn) {
            playBtn.addEventListener("click", () => this.play(video));
        }

        if (pauseBtn) {
            pauseBtn.addEventListener("click", () => this.pause(video));
        }

        if (toggleBtn) {
            toggleBtn.addEventListener("click", () => this.toggle(video));
        }

        if (progress) {

            video.addEventListener("timeupdate", () => {
                if (!video.duration) return;
                progress.value = (video.currentTime / video.duration) * 100;
            });

            progress.addEventListener("input", () => {
                if (!video.duration) return;
                video.currentTime = (progress.value / 100) * video.duration;
            });
        }

        video.addEventListener("play", () => {
            if (this.autoPauseOthers) {
                this.pauseOthers(video);
            }
        });
    }

    play(video) {
        video.play().catch(() => {});
    }

    pause(video) {
        video.pause();
    }

    toggle(video) {

        if (video.paused) {
            this.play(video);
        } else {
            this.pause(video);
        }
    }

    pauseOthers(currentVideo) {

        this.instances.forEach(instance => {

            if (instance.video !== currentVideo) {
                instance.video.pause();
            }

        });
    }

    initIntersectionObserver() {

        const observer = new IntersectionObserver(entries => {

            entries.forEach(entry => {

                const video = entry.target;

                if (!entry.isIntersecting) {
                    video.pause();
                }

            });

        }, {
            threshold: 0.25
        });

        this.instances.forEach(instance => {
            observer.observe(instance.video);
        });
    }
}

export default VideoManager;