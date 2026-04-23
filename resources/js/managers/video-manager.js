/**
 * VideoManager — ES Module (Vite / Laravel)
 *
 * Handles four video source types from a single class:
 *   html5   — server-hosted <video> files
 *   youtube — YouTube IFrame API
 *   vimeo   — Vimeo Player SDK
 *   iframe  — any generic embed URL (Dailymotion, Streamable, etc.)
 *
 * Two usage modes:
 *
 * 1) Manager mode (boot in app.js, auto-scans the page):
 *      new VideoManager({ selector: '[data-video]', autoPauseOthers: true, pauseWhenOutOfView: true })
 *
 * 2) Single-instance mode (programmatic, for one specific container):
 *      const player = VideoManager.create('#hero-video', {
 *        type: 'youtube', src: 'dQw4w9WgXcQ',
 *        thumbnail: '/img/t.jpg', title: 'My Film', badge: 'Brand Film',
 *      });
 *      player.play();
 *      player.swap({ src: 'newVideoId' });
 *      player.destroy();
 */

/* ─── Shared one-time script loader ───────────────────────────── */
const _loaded = {};
const _queues = {};

function loadScript(url, globalReadyKey) {
    return new Promise((resolve, reject) => {
        if (_loaded[url]) { resolve(); return; }
        if (_queues[url]) { _queues[url].push({ resolve, reject }); return; }

        _queues[url] = [{ resolve, reject }];
        const s = document.createElement('script');
        s.src = url; s.async = true;
        s.onerror = () => _queues[url].forEach(p => p.reject(new Error(`Failed: ${url}`)));
        document.head.appendChild(s);

        if (globalReadyKey) {
            const prev = window[globalReadyKey];
            window[globalReadyKey] = (...args) => {
                if (prev) prev(...args);
                _loaded[url] = true;
                _queues[url].forEach(p => p.resolve());
                delete _queues[url];
            };
        } else {
            s.onload = () => { _loaded[url] = true; _queues[url].forEach(p => p.resolve()); delete _queues[url]; };
        }
    });
}

/* ─── SVG icons ───────────────────────────────────────────────── */
const icons = {
    play:       `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z" clip-rule="evenodd"/></svg>`,
    pause:      `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M6.75 5.25a.75.75 0 0 1 .75-.75H9a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H7.5a.75.75 0 0 1-.75-.75V5.25Zm7.5 0A.75.75 0 0 1 15 4.5h1.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H15a.75.75 0 0 1-.75-.75V5.25Z" clip-rule="evenodd"/></svg>`,
    muteOff:    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 4.06c0-1.336-1.616-2.005-2.56-1.06l-4.5 4.5H4.508c-1.141 0-2.318.664-2.66 1.905A9.76 9.76 0 0 0 1.5 12c0 .898.121 1.768.35 2.595.341 1.24 1.518 1.905 2.659 1.905H6.44l4.5 4.5c.945.945 2.561.276 2.561-1.06V4.06ZM17.78 9.22a.75.75 0 1 0-1.06 1.06L18.44 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06l1.72-1.72 1.72 1.72a.75.75 0 1 0 1.06-1.06L20.56 12l1.72-1.72a.75.75 0 1 0-1.06-1.06l-1.72 1.72-1.72-1.72Z"/></svg>`,
    muteOn:     `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 4.06c0-1.336-1.616-2.005-2.56-1.06l-4.5 4.5H4.508c-1.141 0-2.318.664-2.66 1.905A9.76 9.76 0 0 0 1.5 12c0 .898.121 1.768.35 2.595.341 1.24 1.518 1.905 2.659 1.905H6.44l4.5 4.5c.945.945 2.561.276 2.561-1.06V4.06ZM18.584 5.106a.75.75 0 0 1 1.06 0c3.808 3.807 3.808 9.98 0 13.788a.75.75 0 0 1-1.06-1.06 8.25 8.25 0 0 0 0-11.668.75.75 0 0 1 0-1.06ZM15.932 7.757a.75.75 0 0 1 1.061 0 6 6 0 0 1 0 8.486.75.75 0 0 1-1.06-1.061 4.5 4.5 0 0 0 0-6.364.75.75 0 0 1 0-1.06Z"/></svg>`,
    fullscreen: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M15 3.75a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0V5.56l-3.97 3.97a.75.75 0 1 1-1.06-1.06l3.97-3.97h-2.69a.75.75 0 0 1-.75-.75Zm-12 0A.75.75 0 0 1 3.75 3h4.5a.75.75 0 0 1 0 1.5H5.56l3.97 3.97a.75.75 0 0 1-1.06 1.06L4.5 5.56v2.69a.75.75 0 0 1-1.5 0v-4.5Zm11.47 11.78a.75.75 0 1 1 1.06-1.06l3.97 3.97v-2.69a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1 0-1.5h2.69l-3.97-3.97Zm-4.94-1.06a.75.75 0 0 1 0 1.06L5.56 19.5h2.69a.75.75 0 0 1 0 1.5h-4.5a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 1 1.5 0v2.69l3.97-3.97a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>`,
    spinner:    `<svg class="vm-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>`,
};

/* ─── CSS (injected once into <head>) ─────────────────────────── */
function injectStyles() {
    if (document.getElementById('vm-styles')) return;
    const el = document.createElement('style');
    el.id = 'vm-styles';
    el.textContent = `
      .vm-wrap{position:relative;width:100%;aspect-ratio:16/9;background:#0f0f0f;border-radius:1rem;overflow:hidden;cursor:pointer;box-shadow:0 32px 64px -15px rgba(0,0,0,.45);font-family:system-ui,sans-serif;}
      .vm-wrap *{box-sizing:border-box;}
      .vm-embed,.vm-html5{position:absolute;inset:0;width:100%;height:100%;border:none;display:block;}
      .vm-thumbnail{position:absolute;inset:0;z-index:10;transition:opacity .6s ease;}
      .vm-thumbnail img{width:100%;height:100%;object-fit:cover;filter:brightness(.85) grayscale(10%);transition:filter .5s,transform .8s;}
      .vm-wrap:hover .vm-thumbnail img{filter:brightness(.95) grayscale(0);transform:scale(1.03);}
      .vm-thumbnail-gradient{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.65) 0%,transparent 50%,rgba(0,0,0,.25) 100%);}
      .vm-play-btn{position:absolute;inset:0;z-index:20;display:flex;align-items:center;justify-content:center;transition:opacity .4s;}
      .vm-play-inner{position:relative;display:flex;align-items:center;justify-content:center;width:6rem;height:6rem;border-radius:9999px;background:rgba(255,255,255,.1);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.35);color:#fff;transition:background .3s,transform .3s,box-shadow .3s;}
      .vm-play-inner:hover{background:rgba(255,255,255,.25);transform:scale(1.1);box-shadow:0 0 0 8px rgba(255,255,255,.08);}
      .vm-play-inner svg{width:2.5rem;height:2.5rem;margin-left:.25rem;}
      .vm-ring1,.vm-ring2{position:absolute;border-radius:9999px;border:1px solid rgba(255,255,255,.4);animation:vm-ping 3s linear infinite;}
      .vm-ring1{inset:0;opacity:.4;}
      .vm-ring2{inset:-10px;opacity:.18;animation-delay:1s;}
      @keyframes vm-ping{75%,100%{transform:scale(1.4);opacity:0;}}
      .vm-title-wrap{position:absolute;bottom:1.5rem;left:1.5rem;z-index:20;color:#fff;pointer-events:none;transition:transform .4s;}
      .vm-wrap:hover .vm-title-wrap{transform:translateX(4px);}
      .vm-badge{display:inline-block;padding:.2rem .7rem;margin-bottom:.5rem;font-size:.65rem;letter-spacing:.15em;text-transform:uppercase;background:rgba(255,255,255,.18);backdrop-filter:blur(8px);border-radius:9999px;border:1px solid rgba(255,255,255,.15);}
      .vm-title{font-size:1.15rem;font-weight:700;line-height:1.2;text-shadow:0 2px 8px rgba(0,0,0,.6);}
      .vm-subtitle{font-size:.8rem;opacity:.65;margin-top:.25rem;}
      .vm-controls{position:absolute;bottom:0;left:0;right:0;z-index:30;display:flex;align-items:center;gap:.5rem;padding:.6rem .9rem;background:linear-gradient(to top,rgba(0,0,0,.75),transparent);opacity:0;pointer-events:none;transition:opacity .3s;}
      .vm-wrap:hover .vm-controls,.vm-wrap:focus-within .vm-controls{opacity:1;pointer-events:auto;}
      .vm-ctrl-btn{flex-shrink:0;background:none;border:none;cursor:pointer;padding:.3rem;color:#fff;border-radius:.4rem;display:flex;align-items:center;justify-content:center;transition:background .2s;}
      .vm-ctrl-btn:hover{background:rgba(255,255,255,.15);}
      .vm-ctrl-btn svg{width:1.1rem;height:1.1rem;}
      .vm-progress-wrap{flex:1;position:relative;height:.25rem;background:rgba(255,255,255,.25);border-radius:9999px;cursor:pointer;transition:height .2s;}
      .vm-progress-wrap:hover{height:.4rem;}
      .vm-progress-fill{height:100%;background:#fff;border-radius:9999px;pointer-events:none;transition:width .1s linear;}
      .vm-progress-thumb{position:absolute;top:50%;right:0;transform:translate(50%,-50%);width:.8rem;height:.8rem;border-radius:9999px;background:#fff;opacity:0;transition:opacity .2s;}
      .vm-progress-wrap:hover .vm-progress-thumb{opacity:1;}
      .vm-time{font-size:.7rem;color:rgba(255,255,255,.85);white-space:nowrap;min-width:5rem;text-align:right;}
      .vm-spinner{width:2rem;height:2rem;animation:vm-spin 1s linear infinite;color:#fff;}
      @keyframes vm-spin{to{transform:rotate(360deg);}}
      .vm-wrap[data-state="playing"] .vm-thumbnail{opacity:0;pointer-events:none;}
      .vm-wrap[data-state="playing"] .vm-play-btn{opacity:0;pointer-events:none;}
      .vm-wrap[data-state="playing"] .vm-title-wrap{opacity:0;}
      .vm-wrap[data-state="ended"] .vm-thumbnail{opacity:1;pointer-events:auto;}
      .vm-wrap[data-state="ended"] .vm-play-btn{opacity:1;pointer-events:auto;}
      .vm-wrap[data-state="loading"] .vm-play-btn{pointer-events:none;}
      .vm-error{position:absolute;inset:0;z-index:40;display:none;flex-direction:column;align-items:center;justify-content:center;background:rgba(0,0,0,.7);color:#fff;gap:.5rem;}
      .vm-wrap[data-state="error"] .vm-error{display:flex;}
      .vm-error-icon{font-size:2rem;}
      .vm-error-msg{font-size:.85rem;opacity:.75;}
    `;
    document.head.appendChild(el);
}

function fmtTime(s) {
    s = Math.floor(s || 0);
    return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;
}

/* ══════════════════════════════════════════════════════════════════
   VideoInstance — manages one video container element
════════════════════════════════════════════════════════════════════ */
class VideoInstance {

    constructor(el, options = {}) {
        this.el = el;
        this.opts = {
            type:       options.type       || el.dataset.videoType || 'html5',
            src:        options.src        || el.dataset.videoSrc  || '',
            thumbnail:  options.thumbnail  || el.dataset.videoThumbnail || '',
            title:      options.title      || el.dataset.videoTitle     || '',
            subtitle:   options.subtitle   || el.dataset.videoSubtitle  || '',
            badge:      options.badge      || el.dataset.videoBadge     || '',
            autoplay:   options.autoplay   ?? (el.dataset.videoAutoplay === 'true'),
            muted:      options.muted      ?? (el.dataset.videoMuted    === 'true'),
            loop:       options.loop       ?? (el.dataset.videoLoop     === 'true'),
            lazyLoad:   options.lazyLoad   ?? (el.dataset.videoLazy    !== 'false'),
            ytVars:     options.ytVars     || {},
            vimeoOpts:  options.vimeoOpts  || {},
        };

        this._player   = null;
        this._apiReady = false;
        this._state    = 'idle';
        this._io       = null;

        injectStyles();
        this._buildDOM();
        this.opts.lazyLoad ? this._observeViewport() : this._initPlayer();
    }

    /* ── Public API ── */
    play()       { this._cmd('play'); }
    pause()      { this._cmd('pause'); }
    stop()       { this._cmd('stop'); }
    seek(t)      { this._cmd('seek', t); }
    toggleMute() { this._cmd('mute'); }

    /** Hot-swap the video source without rebuilding the overlay chrome */
    swap(newOpts = {}) {
        Object.assign(this.opts, newOpts);
        this._setState('idle');
        if (!this._player || !this._apiReady) return;

        const { type, src } = this.opts;
        try {
            if (type === 'html5')   { this._player.pause(); this._player.src = src; this._player.load(); }
            if (type === 'youtube') { this._player.loadVideoById(src); this._player.stopVideo(); }
            if (type === 'vimeo')   { this._player.loadVideo(src); }
        } catch (_) {}

        if (this._els.thumbImg && this.opts.thumbnail) this._els.thumbImg.src = this.opts.thumbnail;
        if (this._els.title)    this._els.title.textContent    = this.opts.title;
        if (this._els.subtitle) this._els.subtitle.textContent = this.opts.subtitle;
        if (this._els.badge)    this._els.badge.textContent    = this.opts.badge;
    }

    destroy() {
        this._io?.disconnect();
        if (this._player) {
            try {
                if (this.opts.type === 'html5')   this._player.pause();
                if (this.opts.type === 'youtube') this._player.destroy();
                if (this.opts.type === 'vimeo')   this._player.destroy();
            } catch (_) {}
        }
        this.el.innerHTML = '';
        this.el.classList.remove('vm-wrap');
        delete this.el.dataset.state;
    }

    /* ── DOM build ── */
    _buildDOM() {
        this.el.classList.add('vm-wrap');
        this.el.dataset.state = 'idle';

        const thumbHTML = this.opts.thumbnail
            ? `<div class="vm-thumbnail"><img src="${this.opts.thumbnail}" alt="" loading="lazy"><div class="vm-thumbnail-gradient"></div></div>`
            : `<div class="vm-thumbnail"><div class="vm-thumbnail-gradient" style="background:rgba(0,0,0,.4)"></div></div>`;

        const titleHTML = (this.opts.title || this.opts.badge)
            ? `<div class="vm-title-wrap">
                 ${this.opts.badge    ? `<span class="vm-badge">${this.opts.badge}</span>` : ''}
                 ${this.opts.title    ? `<div class="vm-title">${this.opts.title}</div>`   : ''}
                 ${this.opts.subtitle ? `<div class="vm-subtitle">${this.opts.subtitle}</div>` : ''}
               </div>`
            : '';

        const ctrlsHTML = this.opts.type === 'html5'
            ? `<div class="vm-controls">
                 <button class="vm-ctrl-btn" data-vm="playpause">${icons.play}</button>
                 <div class="vm-progress-wrap" data-vm="progress">
                   <div class="vm-progress-fill" style="width:0%"></div>
                   <div class="vm-progress-thumb"></div>
                 </div>
                 <span class="vm-time" data-vm="time">0:00 / 0:00</span>
                 <button class="vm-ctrl-btn" data-vm="muteBtn">${icons.muteOn}</button>
                 <button class="vm-ctrl-btn" data-vm="fsBtn">${icons.fullscreen}</button>
               </div>`
            : '';

        this.el.innerHTML = `
            ${thumbHTML}
            <div class="vm-slot" style="position:absolute;inset:0;"></div>
            <div class="vm-play-btn">
              <div class="vm-play-inner">
                <span class="vm-ring1"></span><span class="vm-ring2"></span>
                ${icons.play}
              </div>
            </div>
            ${titleHTML}
            ${ctrlsHTML}
            <div class="vm-error"><div class="vm-error-icon">⚠</div><div class="vm-error-msg">Failed to load video.</div></div>`;

        this._els = {
            slot:     this.el.querySelector('.vm-slot'),
            thumbImg: this.el.querySelector('.vm-thumbnail img'),
            playBtn:  this.el.querySelector('.vm-play-btn'),
            title:    this.el.querySelector('.vm-title'),
            subtitle: this.el.querySelector('.vm-subtitle'),
            badge:    this.el.querySelector('.vm-badge'),
            ppBtn:    this.el.querySelector('[data-vm="playpause"]'),
            progress: this.el.querySelector('[data-vm="progress"]'),
            fill:     this.el.querySelector('.vm-progress-fill'),
            timeEl:   this.el.querySelector('[data-vm="time"]'),
            muteBtn:  this.el.querySelector('[data-vm="muteBtn"]'),
            fsBtn:    this.el.querySelector('[data-vm="fsBtn"]'),
        };

        this._els.playBtn.addEventListener('click', () => this._onPlayClick());
        if (this.opts.type === 'html5') this._bindHtml5Controls();
    }

    /* ── Lazy load via IntersectionObserver ── */
    _observeViewport() {
        this._io = new IntersectionObserver(entries => {
            if (entries[0].isIntersecting) {
                this._io.disconnect();
                this._initPlayer();
            }
        }, { threshold: 0.1 });
        this._io.observe(this.el);
    }

    /* ── Route to correct player init ── */
    _initPlayer() {
        const t = this.opts.type;
        if      (t === 'html5')   this._initHtml5();
        else if (t === 'youtube') this._initYouTube();
        else if (t === 'vimeo')   this._initVimeo();
        else if (t === 'iframe')  this._initIframe();
        else this._setError(`Unknown type: "${t}"`);
    }

    /* ═══ HTML5 ═══ */
    _initHtml5() {
        const v = document.createElement('video');
        v.className   = 'vm-html5';
        v.src         = this.opts.src;
        v.preload     = 'metadata';
        v.playsInline = true;
        v.muted       = this.opts.muted;
        v.loop        = this.opts.loop;
        if (this.opts.thumbnail) v.poster = this.opts.thumbnail;

        v.addEventListener('loadedmetadata', () => this._updateTime(v));
        v.addEventListener('timeupdate',     () => { this._updateTime(v); this._updateProgress(v); });
        v.addEventListener('play',           () => { this._setState('playing'); this._els.ppBtn && (this._els.ppBtn.innerHTML = icons.pause); });
        v.addEventListener('pause',          () => { this._setState('paused');  this._els.ppBtn && (this._els.ppBtn.innerHTML = icons.play); });
        v.addEventListener('ended',          () => { this._setState('ended');   this._els.ppBtn && (this._els.ppBtn.innerHTML = icons.play); });
        v.addEventListener('waiting',        () => this._setState('loading'));
        v.addEventListener('canplay',        () => { if (this._state === 'loading') this._setState('paused'); });
        v.addEventListener('error',          () => this._setError('Video could not be loaded.'));

        this._els.slot.appendChild(v);
        this._player   = v;
        this._apiReady = true;
        if (this.opts.autoplay) v.play().catch(() => { v.muted = true; v.play(); });
    }

    _bindHtml5Controls() {
        this._els.ppBtn?.addEventListener('click', e => { e.stopPropagation(); this._cmd('play'); });
        this._els.muteBtn?.addEventListener('click', e => {
            e.stopPropagation();
            if (!this._player) return;
            this._player.muted = !this._player.muted;
            this._els.muteBtn.innerHTML = this._player.muted ? icons.muteOff : icons.muteOn;
        });
        this._els.fsBtn?.addEventListener('click', e => {
            e.stopPropagation();
            document.fullscreenElement ? document.exitFullscreen() : this.el.requestFullscreen?.();
        });

        if (this._els.progress) {
            let scrubbing = false;
            const seek = e => {
                if (!this._player?.duration) return;
                const r   = this._els.progress.getBoundingClientRect();
                const pct = Math.min(1, Math.max(0, (e.clientX - r.left) / r.width));
                this._player.currentTime = pct * this._player.duration;
            };
            this._els.progress.addEventListener('mousedown', e => { scrubbing = true; seek(e); });
            document.addEventListener('mousemove', e => { if (scrubbing) seek(e); });
            document.addEventListener('mouseup', () => { scrubbing = false; });
            this._els.progress.addEventListener('touchstart', e => { scrubbing = true; seek(e.touches[0]); }, { passive: true });
            document.addEventListener('touchmove', e => { if (scrubbing) seek(e.touches[0]); }, { passive: true });
            document.addEventListener('touchend', () => { scrubbing = false; });
        }
    }

    _updateProgress(v) {
        if (!this._els.fill || !v.duration) return;
        const pct = (v.currentTime / v.duration) * 100;
        this._els.fill.style.width = `${pct}%`;
    }

    _updateTime(v) {
        if (this._els.timeEl) this._els.timeEl.textContent = `${fmtTime(v.currentTime)} / ${fmtTime(v.duration)}`;
    }

    /* ═══ YouTube ═══ */
    _initYouTube() {
        this._setState('loading');
        loadScript('https://www.youtube.com/iframe_api', 'onYouTubeIframeAPIReady').then(() => {
            this._player = new YT.Player(this._mkDiv(), {
                videoId: this.opts.src,
                playerVars: Object.assign({ autoplay:0, controls:1, rel:0, modestbranding:1, iv_load_policy:3, origin: window.location.origin }, this.opts.ytVars),
                events: {
                    onReady:       () => { this._apiReady = true; this._setState('idle'); if (this.opts.autoplay) this._player.playVideo(); },
                    onStateChange: e  => {
                        const S = YT.PlayerState;
                        if (e.data === S.PLAYING)   this._setState('playing');
                        if (e.data === S.PAUSED)    this._setState('paused');
                        if (e.data === S.ENDED)     this._setState('ended');
                        if (e.data === S.BUFFERING) this._setState('loading');
                    },
                    onError: () => this._setError('YouTube player error.'),
                },
            });
        }).catch(() => this._setError('Could not load YouTube API.'));
    }

    /* ═══ Vimeo ═══ */
    _initVimeo() {
        this._setState('loading');
        loadScript('https://player.vimeo.com/api/player.js').then(() => {
            const div  = this._mkDiv();
            this._player = new Vimeo.Player(div, Object.assign({ id: this.opts.src, width: this.el.offsetWidth, muted: this.opts.muted, loop: this.opts.loop, autoplay: false }, this.opts.vimeoOpts));
            this._player.ready().then(() => { this._apiReady = true; this._setState('idle'); if (this.opts.autoplay) this._player.play(); });
            this._player.on('play',  () => this._setState('playing'));
            this._player.on('pause', () => this._setState('paused'));
            this._player.on('ended', () => this._setState('ended'));
            this._player.on('error', () => this._setError('Vimeo player error.'));
        }).catch(() => this._setError('Could not load Vimeo API.'));
    }

    /* ═══ Generic iframe ═══ */
    _initIframe() {
        const iframe = document.createElement('iframe');
        iframe.className = 'vm-embed';
        iframe.src = this.opts.src;
        iframe.allow = 'autoplay; fullscreen; picture-in-picture';
        iframe.allowFullscreen = true;
        this._els.slot.appendChild(iframe);
        this._player   = iframe;
        this._apiReady = true;
        this._setState('idle');
    }

    /* ── Shared helpers ── */
    _mkDiv() {
        const d = document.createElement('div');
        d.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;';
        this._els.slot.appendChild(d);
        return d;
    }

    _setState(s) { this._state = s; this.el.dataset.state = s; }

    _setError(msg) {
        this._setState('error');
        const m = this.el.querySelector('.vm-error-msg');
        if (m) m.textContent = msg;
    }

    _onPlayClick() {
        if (!this._apiReady) {
            const inner = this._els.playBtn.querySelector('.vm-play-inner');
            if (inner) inner.innerHTML = icons.spinner;
            if (!this._player) this._initPlayer();
            return;
        }
        this._cmd('play');
    }

    _cmd(action, value) {
        const p = this._player;
        if (!p || !this._apiReady) return;
        const t = this.opts.type;

        if (t === 'html5') {
            if (action === 'play')  p.paused ? p.play() : p.pause();
            if (action === 'pause') p.pause();
            if (action === 'stop')  { p.pause(); p.currentTime = 0; }
            if (action === 'seek')  p.currentTime = value;
            if (action === 'mute')  p.muted = !p.muted;
        }
        if (t === 'youtube') {
            if (action === 'play')  p.getPlayerState() === YT.PlayerState.PLAYING ? p.pauseVideo() : p.playVideo();
            if (action === 'pause') p.pauseVideo();
            if (action === 'stop')  p.stopVideo();
            if (action === 'seek')  p.seekTo(value, true);
            if (action === 'mute')  p.isMuted() ? p.unMute() : p.mute();
        }
        if (t === 'vimeo') {
            if (action === 'play')  p.getPaused().then(paused => paused ? p.play() : p.pause());
            if (action === 'pause') p.pause();
            if (action === 'stop')  { p.pause(); p.setCurrentTime(0); }
            if (action === 'seek')  p.setCurrentTime(value);
            if (action === 'mute')  p.getMuted().then(m => p.setMuted(!m));
        }
    }
}

/* ══════════════════════════════════════════════════════════════════
   VideoManager — scans the page and manages multiple VideoInstances
   This is what app.js boots with new VideoManager({ ... })
════════════════════════════════════════════════════════════════════ */
class VideoManager {

    constructor(options = {}) {
        this.selector           = options.selector           || '[data-video]';
        this.autoPauseOthers    = options.autoPauseOthers    ?? true;
        this.pauseWhenOutOfView = options.pauseWhenOutOfView ?? true;
        this.instances          = [];

        this._scan();
        if (this.pauseWhenOutOfView) this._initPauseObserver();
    }

    /** Create and return a single VideoInstance for programmatic use */
    static create(containerOrSelector, options = {}) {
        const el = typeof containerOrSelector === 'string'
            ? document.querySelector(containerOrSelector)
            : containerOrSelector;
        if (!el) throw new Error(`VideoManager.create: element not found — "${containerOrSelector}"`);
        return new VideoInstance(el, options);
    }

    /** Add a new element to manager's tracking after initial scan */
    add(el, options = {}) {
        if (this.instances.find(i => i.el === el)) return;
        const inst = new VideoInstance(el, options);
        this.instances.push(inst);
        this._wireAutoPause(inst);
        return inst;
    }

    /** Destroy all instances */
    destroyAll() {
        this.instances.forEach(i => i.destroy());
        this.instances = [];
    }

    _scan() {
        document.querySelectorAll(this.selector).forEach(el => {
            if (el._vmInstance) return;
            const inst = new VideoInstance(el);
            el._vmInstance = inst;
            this.instances.push(inst);
            this._wireAutoPause(inst);
        });
    }

    _wireAutoPause(inst) {
        if (!this.autoPauseOthers || inst.opts.type !== 'html5') return;

        inst.el.addEventListener('play', () => {
            this.instances.forEach(other => {
                if (other !== inst && other._player && other.opts.type === 'html5') {
                    other._player.pause();
                }
            });
        }, true);
    }

    _initPauseObserver() {
        const io = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                const inst = this.instances.find(i => i.el === entry.target);
                if (inst && !entry.isIntersecting) inst.pause();
            });
        }, { threshold: 0.25 });

        this.instances.forEach(i => io.observe(i.el));
    }
}

export default VideoManager;
export { VideoInstance };
