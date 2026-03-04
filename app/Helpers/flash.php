<?php

if (!function_exists('flash')) {
    /**
     * Add a flash message to the session
     *
     * @param string $message The message to display
     * @param string $type Type: success, error, warning, info
     * @param int $duration Duration in milliseconds (0 = no auto-dismiss)
     * @param string|null $description Optional description text
     * @return void
     */
    function flash($message, $type = 'success', $duration = 5000, $description = null)
    {
        session()->flash('flash_message', [
            'message' => $message,
            'type' => $type,
            'duration' => $duration,
            'description' => $description,
        ]);
    }
}

if (!function_exists('flash_now')) {
    /**
     * Add a flash message for the current request only
     *
     * @param string $message
     * @param string $type
     * @param int $duration
     * @param string|null $description
     * @return void
     */
    function flash_now($message, $type = 'success', $duration = 5000, $description = null)
    {
        session()->now('flash_message', [
            'message' => $message,
            'type' => $type,
            'duration' => $duration,
            'description' => $description,
        ]);
    }
}

if (!function_exists('flash_multiple')) {
    /**
     * Add multiple flash messages
     *
     * @param array $messages Array of message arrays
     * @return void
     */
    function flash_multiple(array $messages)
    {
        session()->flash('flash_messages', $messages);
    }
}

if (!function_exists('flash_success')) {
    /**
     * Add a success flash message
     *
     * @param string $message
     * @param int $duration
     * @param string|null $description
     * @return void
     */
    function flash_success($message, $duration = 5000, $description = null)
    {
        flash($message, 'success', $duration, $description);
    }
}

if (!function_exists('flash_error')) {
    /**
     * Add an error flash message
     *
     * @param string $message
     * @param int $duration
     * @param string|null $description
     * @return void
     */
    function flash_error($message, $duration = 5000, $description = null)
    {
        flash($message, 'error', $duration, $description);
    }
}

if (!function_exists('flash_warning')) {
    /**
     * Add a warning flash message
     *
     * @param string $message
     * @param int $duration
     * @param string|null $description
     * @return void
     */
    function flash_warning($message, $duration = 5000, $description = null)
    {
        flash($message, 'warning', $duration, $description);
    }
}

if (!function_exists('flash_info')) {
    /**
     * Add an info flash message
     *
     * @param string $message
     * @param int $duration
     * @param string|null $description
     * @return void
     */
    function flash_info($message, $duration = 5000, $description = null)
    {
        flash($message, 'info', $duration, $description);
    }
}

if (!function_exists('flash_persistent')) {
    /**
     * Add a persistent flash message (no auto-dismiss)
     *
     * @param string $message
     * @param string $type
     * @param string|null $description
     * @return void
     */
    function flash_persistent($message, $type = 'info', $description = null)
    {
        flash($message, $type, 0, $description);
    }
}
