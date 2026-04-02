<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function set_flash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_messages'][$type][] = $message;
}

function get_flash_messages() {
    if (empty($_SESSION['flash_messages'])) {
        return [];
    }

    $messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
    return $messages;
}

function render_flash_messages() {
    $messages = get_flash_messages();
    if (empty($messages)) {
        return;
    }

    foreach ($messages as $type => $group) {
        foreach ($group as $message) {
            $bg = $type === 'error' ? 'rgba(220, 38, 38, 0.1)' : 'rgba(16, 185, 129, 0.12)';
            $border = $type === 'error' ? 'rgba(220, 38, 38, 0.5)' : 'rgba(16, 185, 129, 0.6)';
            echo "<div style=\"padding:0.85rem 1rem;margin-bottom:1rem;border:1px solid {$border};background:{$bg};border-radius:12px;color:var(--text);\">" . htmlspecialchars($message) . "</div>";
        }
    }
}
