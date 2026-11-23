<?php

return [
    'default_timeout' => (int) env('AI_DEFAULT_TIMEOUT', 60),
    'max_history_messages' => (int) env('AI_MAX_HISTORY_MESSAGES', 20),
    'max_message_chars' => (int) env('AI_MAX_MESSAGE_CHARS', 20000),
];
