<?php

return ['ping_binary' => env('PING_BINARY', '/bin/ping'), 'history_retention_days' => (int) env('MONITORING_HISTORY_RETENTION_DAYS', 30), 'queue' => env('MONITORING_QUEUE', 'monitoring'), 'down_notification_delay_minutes' => (int) env('DOWN_NOTIFICATION_DELAY_MINUTES', 5)];
