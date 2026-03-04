{{-- Flash Messages Container - Auto-initializes --}}
<div class="flash-messages-container fixed top-4 right-4 z-[9999] space-y-3 w-full max-w-sm pointer-events-none"></div>

{{-- Process session flash messages --}}
@if (session()->has('flash_message'))
    @php
        $flash = session('flash_message');
        // Ensure all required fields exist
        $flash = array_merge(
            [
                'message' => '',
                'type' => 'success',
                'duration' => 5000,
                'description' => '',
            ],
            $flash,
        );
    @endphp

    <script>
        // Queue the message for when flash system is ready
        (function() {
            const flashData = {
                message: @json($flash['message']),
                type: @json($flash['type']),
                duration: @json($flash['duration']),
                description: @json($flash['description'])
            };

            // Try to show immediately if flash is available
            if (typeof window.flash === 'function') {
                window.flash(
                    flashData.message,
                    flashData.type,
                    flashData.duration,
                    flashData.description
                );
            } else {
                // Queue for later
                if (!window.queuedFlashMessages) {
                    window.queuedFlashMessages = [];
                }
                window.queuedFlashMessages.push(flashData);

                // Try again after a delay
                setTimeout(function() {
                    if (typeof window.flash === 'function' && window.queuedFlashMessages) {
                        window.queuedFlashMessages.forEach(function(msg) {
                            window.flash(msg.message, msg.type, msg.duration, msg.description);
                        });
                        window.queuedFlashMessages = [];
                    }
                }, 100);
            }
        })();
    </script>
@endif

{{-- Process multiple flash messages if they exist --}}
@if (session()->has('flash_messages'))
    @php
        $flashMessages = session('flash_messages');
    @endphp

    <script>
        (function() {
            const messages = @json($flashMessages);

            const showMessages = function() {
                if (typeof window.flash === 'function') {
                    messages.forEach(function(msg, index) {
                        setTimeout(function() {
                            window.flash(
                                msg.message || '',
                                msg.type || 'success',
                                msg.duration || 5000,
                                msg.description || ''
                            );
                        }, index * 200); // Stagger messages
                    });
                } else {
                    // Queue for later
                    if (!window.queuedFlashMessages) {
                        window.queuedFlashMessages = [];
                    }
                    window.queuedFlashMessages.push(...messages);
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showMessages);
            } else {
                showMessages();
            }
        })();
    </script>
@endif
