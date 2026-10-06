<?php
/**
 * Growfy Agency — Public one-page site.
 * Handles contact / order / newsletter submissions, then renders sections.
 */
require_once __DIR__ . '/includes/bootstrap.php';

/* ------------------------- form submissions ------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $ajax  = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') || !empty($_POST['_ajax']);
    $fail  = function (string $msg, int $code = 422) use ($ajax) {
        if ($ajax) { http_response_code($code); header('Content-Type: application/json'); echo json_encode(['ok' => false, 'msg' => $msg]); exit; }
        flash_set('error', $msg);
        redirect('index.php#contact');
    };
    $done  = function (string $msg, string $anchor = '#contact') use ($ajax) {
        if ($ajax) { header('Content-Type: application/json'); echo json_encode(['ok' => true, 'msg' => $msg]); exit; }
        flash_set('success', $msg);
        redirect('index.php' . $anchor);
    };

    csrf_verify();

    // honeypot — silently accept spammy bots
    if (post('website') !== '') {
        $done('Thank you! We will get back to you shortly.');
    }

    // throttle (one submission / 8s / session)
    $now = time();
    if (!empty($_SESSION['last_form']) && ($now - (int) $_SESSION['last_form']) < 8) {
        $fail('Please wait a few seconds before submitting again.');
    }

    $action = post('action');

    if ($action === 'contact') {
        $name = field('name', 120); $email = field('email', 150); $message = field('message', 3000);
        if ($name === '' || $email === '' || $message === '') $fail('Please fill in your name, email and message.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $fail('Please enter a valid email address.');
        db()->prepare('INSERT INTO ' . DB_PREFIX . 'messages (name, email, phone, subject, message, ip) VALUES (?,?,?,?,?,?)')
            ->execute([$name, $email, field('phone', 60, false), field('subject', 200, false), $message, client_ip()]);
        $_SESSION['last_form'] = $now;
        $done('Message received! Our team will reply within a few hours.');
    }

    if ($action === 'order') {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $service = $serviceId ? q_one('services', 'id = ?', [$serviceId]) : null;
        $name = field('name', 120); $contactInfo = field('contact', 150);
        if (!$service) $fail('Please choose a valid service.');
        if ($name === '' || $contactInfo === '') $fail('Please add your name and a way to reach you.');
        db()->prepare('INSERT INTO ' . DB_PREFIX . 'orders (service_id, service_name, package, name, contact, link, notes, ip) VALUES (?,?,?,?,?,?,?,?)')
            ->execute([$service['id'], $service['title'], field('package', 60, false) ?: 'Starter',
                $name, $contactInfo, field('link', 255, false), field('notes', 1000, false), client_ip()]);
        $_SESSION['last_form'] = $now;
        $done('Order request placed! We will contact you shortly to confirm.', '#services');
    }

    if ($action === 'newsletter') {
        $email = field('email', 150);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $fail('Please enter a valid email address.');
        $st = db()->prepare('INSERT INTO ' . DB_PREFIX . 'subscribers (email) VALUES (?)');
        try { $st->execute([$email]); } catch (Throwable $e) { /* already subscribed */ }
        $_SESSION['last_form'] = $now;
        $done('Subscribed! Growth tips are on the way to your inbox.', '#footer');
    }

    $fail('Unknown request.', 400);
}

/* ------------------------------- data -------------------------------- */
$services     = q_all('services', 'active = 1');
$stats        = q_all('stats', 'active = 1');
$steps        = q_all('steps', 'active = 1');
$features     = q_all('features', 'active = 1');
$testimonials = q_all('testimonials', 'active = 1');
$faqs         = q_all('faqs', 'active = 1');

require __DIR__ . '/includes/site_header.php';

$sections = ['hero', 'marquee', 'stats', 'about', 'services', 'process', 'why', 'testimonials', 'faq', 'cta', 'contact'];
foreach ($sections as $section) {
    require __DIR__ . '/includes/sections/' . $section . '.php';
}

require __DIR__ . '/includes/site_footer.php';
