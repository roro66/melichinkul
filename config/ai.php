<?php

return [
    /*
    | Asistente de preguntas sobre los datos y el manual.
    | Usa Gemini. Salen la pregunta y las filas del SELECT (con tope).
    | La base completa no se envía. Solo lectura.
    */
    'enabled' => (bool) env('AI_ASSISTANT_ENABLED', false),

    'provider' => env('AI_PROVIDER', 'gemini'),

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash'),
        'fallback_models' => array_values(array_filter(array_map('trim', explode(',', (string) env(
            'GEMINI_FALLBACK_MODELS',
            'gemini-3.6-flash,gemini-3.1-flash-lite,gemini-3.5-flash-lite'
        ))))),
        'retry_rounds' => (int) env('GEMINI_RETRY_ROUNDS', 2),
        'retry_wait_ms' => (int) env('GEMINI_RETRY_WAIT_MS', 2500),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 45),
    ],

    'max_tool_rounds' => 5,
    'max_rows_to_model' => 40,
    'max_sql_rows' => 200,
    'sql_timeout_ms' => 8000,

    'sql_connection' => env('AI_SQL_CONNECTION', 'pgsql_ai'),
    'sql_role' => env('AI_DB_USERNAME', 'melichinkul_ai'),
    'sql_set_role' => (bool) env('AI_SQL_SET_ROLE', false),
];
