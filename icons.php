<?php
/**
 * Minimal hand-drawn medical icon set — plain inline SVG, no external library.
 * Usage: <?= icon('heart-pulse') ?>
 */
function icon(string $name): string {
    $stroke = 'stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"';
    $icons = [
        'heart-pulse' => '<path '.$stroke.' d="M4 12h3l2 5 3-10 2 5h6"/><path '.$stroke.' d="M12 20s-7-4.4-9-9.6C1.6 6.8 4 4 7 4c2 0 3.5 1.2 5 3 1.5-1.8 3-3 5-3 3 0 5.4 2.8 4 6.4C19 15.6 12 20 12 20z"/>',
        'pill' => '<rect x="3" y="10" width="18" height="8" rx="4" '.$stroke.'/><line x1="12" y1="10" x2="12" y2="18" '.$stroke.'/>',
        'calendar-heart' => '<rect x="3" y="5" width="18" height="16" rx="2" '.$stroke.'/><line x1="3" y1="10" x2="21" y2="10" '.$stroke.'/><path '.$stroke.' d="M12 17s-3-1.8-3-3.8c0-1.2 1-2.2 2.2-2.2.7 0 1.3.3 1.8.9.5-.6 1.1-.9 1.8-.9 1.2 0 2.2 1 2.2 2.2C17 15.2 12 17 12 17z"/>',
        'house-medical' => '<path '.$stroke.' d="M4 11l8-6 8 6"/><path '.$stroke.' d="M6 10v9h12v-9"/><line x1="12" y1="12" x2="12" y2="17" '.$stroke.'/><line x1="9.5" y1="14.5" x2="14.5" y2="14.5" '.$stroke.'/>',
        'nurse' => '<circle cx="12" cy="7" r="3.2" '.$stroke.'/><path '.$stroke.' d="M5 21c0-4 3-6.5 7-6.5s7 2.5 7 6.5"/><line x1="10.5" y1="8.5" x2="13.5" y2="8.5" '.$stroke.'/><line x1="12" y1="7" x2="12" y2="10" '.$stroke.'/>',
        'ambulance' => '<rect x="2" y="9" width="13" height="8" rx="1.5" '.$stroke.'/><path '.$stroke.' d="M15 12h3l3 3v2h-6"/><circle cx="6.5" cy="18.5" r="1.6" '.$stroke.'/><circle cx="17" cy="18.5" r="1.6" '.$stroke.'/><line x1="7" y1="11" x2="7" y2="14.5" '.$stroke.'/><line x1="5.2" y1="12.7" x2="8.8" y2="12.7" '.$stroke.'/>',
        'flask' => '<path '.$stroke.' d="M10 3h4"/><path '.$stroke.' d="M10.5 3.5v6L5 18.5A2 2 0 0 0 7 21h10a2 2 0 0 0 2-2.5L13.5 9.5v-6"/><line x1="8" y1="15" x2="16" y2="15" '.$stroke.'/>',
        'file-upload' => '<path '.$stroke.' d="M6 3h8l4 4v14H6z"/><path '.$stroke.' d="M14 3v4h4"/><line x1="12" y1="12" x2="12" y2="18" '.$stroke.'/><polyline points="9.5,14.5 12,12 14.5,14.5" '.$stroke.'/>',
        'syringe' => '<line x1="20" y1="4" x2="16" y2="8" '.$stroke.'/><line x1="17.5" y1="5.5" x2="19" y2="7" '.$stroke.'/><path '.$stroke.' d="M15 7l3 3-9 9H6v-3z"/><line x1="7" y1="17" x2="4" y2="20" '.$stroke.'/>',
        'stethoscope' => '<path '.$stroke.' d="M6 3v6a4 4 0 0 0 8 0V3"/><path '.$stroke.' d="M10 13v2a5 5 0 0 0 10 0v-2"/><circle cx="20" cy="11" r="1.6" '.$stroke.'/>',
        'admin' => '<circle cx="12" cy="8" r="3" '.$stroke.'/><path '.$stroke.' d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6"/><path '.$stroke.' d="M12 2l1 2 2 .3-1.4 1.4.3 2-1.9-1-1.9 1 .3-2L9 4.3 11 4z"/>',
        'users' => '<circle cx="9" cy="8" r="3" '.$stroke.'/><circle cx="16" cy="9" r="2.4" '.$stroke.'/><path '.$stroke.' d="M3.5 20c0-3 2.5-5.2 5.5-5.2s5.5 2.2 5.5 5.2"/><path '.$stroke.' d="M14.5 15.2c2.4.2 4 2 4 4.8"/>',
        'patients' => '<circle cx="12" cy="7" r="3" '.$stroke.'/><path '.$stroke.' d="M5 21c0-4 3-6.5 7-6.5s7 2.5 7 6.5"/><path '.$stroke.' d="M9 11.5l3 2 3-2"/>',
        'physicians' => '<circle cx="12" cy="7" r="3" '.$stroke.'/><path '.$stroke.' d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6"/><line x1="12" y1="12.5" x2="12" y2="16.5" '.$stroke.'/><line x1="10" y1="14.5" x2="14" y2="14.5" '.$stroke.'/>',
        'services' => '<circle cx="12" cy="12" r="8.5" '.$stroke.'/><path '.$stroke.' d="M12 7v5l3 3"/>',
        'transaction' => '<rect x="3" y="6" width="18" height="13" rx="2" '.$stroke.'/><line x1="3" y1="10" x2="21" y2="10" '.$stroke.'/><line x1="7" y1="14" x2="11" y2="14" '.$stroke.'/>',
        'userdetails' => '<rect x="4" y="4" width="16" height="16" rx="2" '.$stroke.'/><circle cx="9" cy="10" r="2" '.$stroke.'/><line x1="13" y1="9" x2="17" y2="9" '.$stroke.'/><line x1="13" y1="12" x2="17" y2="12" '.$stroke.'/><line x1="7" y1="16" x2="17" y2="16" '.$stroke.'/>',
        'room' => '<path '.$stroke.' d="M4 21V9l8-5 8 5v12"/><line x1="9" y1="21" x2="9" y2="14" '.$stroke.'/><line x1="15" y1="21" x2="15" y2="14" '.$stroke.'/>',
        'discount' => '<circle cx="12" cy="12" r="9" '.$stroke.'/><line x1="8" y1="16" x2="16" y2="8" '.$stroke.'/><circle cx="9" cy="9" r="1" '.$stroke.'/><circle cx="15" cy="15" r="1" '.$stroke.'/>',
        'appointment' => '<rect x="3" y="5" width="18" height="16" rx="2" '.$stroke.'/><line x1="3" y1="10" x2="21" y2="10" '.$stroke.'/><polyline points="9,14 11,16 15,12" '.$stroke.'/>',
        'doctor' => '<circle cx="12" cy="7" r="3" '.$stroke.'/><path '.$stroke.' d="M5 21c0-4 3-6.5 7-6.5s7 2.5 7 6.5"/><path '.$stroke.' d="M9.5 4.5v3a2.5 2.5 0 0 0 5 0v-3"/>',
        'specialization' => '<path '.$stroke.' d="M12 3l2.2 4.6 5 .7-3.6 3.6.9 5-4.5-2.4-4.5 2.4.9-5-3.6-3.6 5-.7z"/>',
        'star' => '<path '.$stroke.' d="M12 3l2.2 4.6 5 .7-3.6 3.6.9 5-4.5-2.4-4.5 2.4.9-5-3.6-3.6 5-.7z"/>',
        'lock' => '<rect x="5" y="11" width="14" height="9" rx="2" '.$stroke.'/><path '.$stroke.' d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2" '.$stroke.'/><polyline points="3,6 12,13 21,6" '.$stroke.'/>',
    ];
    $body = $icons[$name] ?? $icons['heart-pulse'];
    return '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">' . $body . '</svg>';
}
