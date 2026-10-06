<?php
/**
 * Database schema (MySQL + SQLite) and first-run seed data.
 * Everything the site needs to look complete out of the box.
 */

function schema_statements(string $driver): array
{
    $p = DB_PREFIX;
    if ($driver === 'mysql') {
        $ai   = 'INT AUTO_INCREMENT PRIMARY KEY';
        $txt  = 'TEXT';
        $eng  = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        $int  = 'INT';
        $tiny = 'TINYINT(1)';
        $var  = fn($n) => "VARCHAR($n)";
        $time = 'DATETIME';
        $now  = 'CURRENT_TIMESTAMP';
    } else {
        $ai   = 'INTEGER PRIMARY KEY AUTOINCREMENT';
        $txt  = 'TEXT';
        $eng  = '';
        $int  = 'INTEGER';
        $tiny = 'INTEGER';
        $var  = fn($n) => "VARCHAR($n)";
        $time = 'TEXT';
        $now  = "CURRENT_TIMESTAMP";
    }

    return [
        "CREATE TABLE IF NOT EXISTS {$p}settings (
            k {$var(80)} PRIMARY KEY,
            v {$txt}
        )$eng",
        "CREATE TABLE IF NOT EXISTS {$p}admins (
            id $ai,
            username {$var(60)} NOT NULL UNIQUE,
            pass_hash {$var(255)} NOT NULL,
            display_name {$var(120)} DEFAULT '',
            last_login {$time} NULL,
            force_change $tiny NOT NULL DEFAULT 0,
            created_at {$time} DEFAULT $now
        )$eng",
        "CREATE TABLE IF NOT EXISTS {$p}services (
            id $ai,
            title {$var(150)} NOT NULL,
            tag {$var(60)} DEFAULT '',
            platform {$var(60)} DEFAULT '',
            icon {$var(40)} DEFAULT 'spark',
            description {$txt},
            price_from {$var(60)} DEFAULT '',
            sort_order $int DEFAULT 0,
            active $tiny NOT NULL DEFAULT 1,
            created_at {$time} DEFAULT $now
        )$eng",
        "CREATE TABLE IF NOT EXISTS {$p}stats (
            id $ai,
            value {$var(20)} NOT NULL DEFAULT '0',
            suffix {$var(10)} DEFAULT '',
            label {$var(150)} NOT NULL,
            sublabel {$var(150)} DEFAULT '',
            icon {$var(40)} DEFAULT 'users',
            sort_order $int DEFAULT 0,
            active $tiny NOT NULL DEFAULT 1
        )$eng",
        "CREATE TABLE IF NOT EXISTS {$p}steps (
            id $ai,
            title {$var(150)} NOT NULL,
            description {$txt},
            icon {$var(40)} DEFAULT 'brush',
            sort_order $int DEFAULT 0,
            active $tiny NOT NULL DEFAULT 1
        )$eng",
        "CREATE TABLE IF NOT EXISTS {$p}features (
            id $ai,
            title {$var(150)} NOT NULL,
            description {$txt},
            icon {$var(40)} DEFAULT 'bolt',
            sort_order $int DEFAULT 0,
            active $tiny NOT NULL DEFAULT 1
        )$eng",
        "CREATE TABLE IF NOT EXISTS {$p}testimonials (
            id $ai,
            name {$var(120)} NOT NULL,
            role {$var(120)} DEFAULT '',
            avatar {$var(255)} DEFAULT '',
            quote {$txt},
            stars $tiny DEFAULT 5,
            sort_order $int DEFAULT 0,
            active $tiny NOT NULL DEFAULT 1
        )$eng",
        "CREATE TABLE IF NOT EXISTS {$p}faqs (
            id $ai,
            question {$var(255)} NOT NULL,
            answer {$txt},
            sort_order $int DEFAULT 0,
            active $tiny NOT NULL DEFAULT 1
        )$eng",
        "CREATE TABLE IF NOT EXISTS {$p}messages (
            id $ai,
            name {$var(120)} NOT NULL,
            email {$var(150)} DEFAULT '',
            phone {$var(60)} DEFAULT '',
            subject {$var(200)} DEFAULT '',
            message {$txt},
            ip {$var(60)} DEFAULT '',
            is_read $tiny NOT NULL DEFAULT 0,
            created_at {$time} DEFAULT $now
        )$eng",
        "CREATE TABLE IF NOT EXISTS {$p}orders (
            id $ai,
            service_id $int DEFAULT NULL,
            service_name {$var(150)} DEFAULT '',
            package {$var(60)} DEFAULT '',
            name {$var(120)} NOT NULL,
            contact {$var(150)} NOT NULL,
            link {$var(255)} DEFAULT '',
            notes {$txt},
            status {$var(20)} NOT NULL DEFAULT 'new',
            ip {$var(60)} DEFAULT '',
            created_at {$time} DEFAULT $now
        )$eng",
        "CREATE TABLE IF NOT EXISTS {$p}subscribers (
            id $ai,
            email {$var(150)} NOT NULL UNIQUE,
            created_at {$time} DEFAULT $now
        )$eng",
    ];
}

/** Default site content — editable from the admin panel. */
function seed_settings(): array
{
    return [
        // identity
        'site_name'        => 'Growfy Agency',
        'site_tagline'     => 'Digital Growth, Engineered.',
        'seo_title'        => 'Growfy Agency — Social Media Growth & Digital Marketing',
        'seo_description'  => 'Growfy Agency helps brands scale with data-driven social media growth, premium design and full-funnel digital marketing. Instagram, YouTube, TikTok, Spotify & more.',
        'seo_keywords'     => 'social media growth, digital marketing agency, instagram growth, youtube promotion, tiktok marketing',
        // contact
        'contact_email'    => 'hello@growfy.info',
        'contact_phone'    => '+1 (555) 010-2233',
        'whatsapp_number'  => '15550102233',
        'contact_address'  => 'Dhaka · Dubai · Remote Worldwide',
        'business_hours'   => 'Sat – Thu · 10:00 – 22:00',
        // socials
        'social_facebook'  => 'https://facebook.com/growfy',
        'social_instagram' => 'https://instagram.com/growfy',
        'social_twitter'   => 'https://x.com/growfy',
        'social_linkedin'  => 'https://linkedin.com/company/growfy',
        'social_youtube'   => 'https://youtube.com/@growfy',
        'social_tiktok'    => 'https://tiktok.com/@growfy',
        // hero
        'hero_badge'       => '#1 Social Growth Studio',
        'hero_title'       => 'Empowering Brands in the Digital Age',
        'hero_highlight'   => 'Empowering Brands',
        'hero_subtitle'    => 'We combine data-driven strategy with creative excellence to keep your business relevant, visible and growing — across every platform that matters.',
        'hero_cta1_text'   => 'Get Started',
        'hero_cta2_text'   => 'Explore Services',
        'hero_card1_label' => 'Followers Growth',
        'hero_card1_value' => '+248.6%',
        'hero_card2_label' => 'Engagement Rate',
        'hero_card2_value' => '8.4%',
        'hero_card3_label' => 'Monthly Reach',
        'hero_card3_value' => '2.1M',
        // about
        'about_kicker'     => 'Start your business with our services',
        'about_title'      => 'Welcome to Growfy Agency',
        'about_text'       => "At Growfy, we blend cutting-edge technology with strategic creative thinking. Whether it's digital marketing, graphic design, or web development — our mission is to scale your brand through measurable results and premium user experiences.",
        'about_points'     => "Dedicated growth manager for every account\nPremium quality, platform-safe methods\nWeekly transparent performance reports",
        // services section
        'services_kicker'  => 'Boost Your Presence',
        'services_title'   => 'Our Most Ordered Services',
        'services_text'    => 'Pick a platform — our specialists build a growth system around it. Real strategy, real audiences, measurable momentum.',
        // process
        'steps_kicker'     => 'How We Work',
        'steps_title'      => 'Accelerating Your Social Media Growth',
        'steps_text'       => 'A proven operating system for organic and paid growth — refined across hundreds of brands.',
        // why us
        'why_kicker'       => 'The Growfy Edge',
        'why_title'        => 'Why Choose Growfy Agency?',
        'why_lead1'        => 'Growfy Agency helps your brand stand out with visually striking, meaningful and emotionally engaging content. We blend modern design trends with powerful storytelling so your message connects instantly.',
        'why_lead2'        => 'Beyond design, we deliver smart, data-driven digital marketing. Our SEO, PPC and social media management ensure higher visibility and better-quality traffic for long-term growth.',
        // testimonials
        'testimonials_kicker' => 'Global Impact',
        'testimonials_title'  => 'Our Elite Partnerships',
        // faq
        'faq_kicker'       => 'Questions & Answers',
        'faq_title'        => 'Frequently Asked Questions',
        // cta banner
        'cta_title'        => 'Ready to grow your brand?',
        'cta_text'         => 'Get a free growth audit of your social profiles — no commitment, just clarity on your next 90 days.',
        'cta_button'       => 'Claim Free Audit',
        // contact
        'contact_kicker'   => 'Let’s Talk',
        'contact_title'    => 'Tell Us About Your Project',
        'contact_text'     => 'Drop a line and our strategists will get back within a few hours with a tailored plan.',
        // footer
        'footer_about'     => 'Growfy is a full-stack growth studio helping creators, artists and businesses win attention that converts.',
        'footer_copyright' => 'Growfy Agency. All rights reserved.',
    ];
}

function seed_rows(): array
{
    return [
        'services' => [
            ['Instagram', 'BEST SELLER', 'instagram', 'instagram', 'Grow your followers and engagement with premium organic strategies, reel trends and smart hashtags.', '$29', 1],
            ['YouTube Service', 'TRENDING', 'youtube', 'youtube', 'Increase views, watch time and subscribers — everything your channel needs for monetization milestones.', '$39', 2],
            ['Facebook Service', '', 'facebook', 'facebook', 'Professional page growth, reach boosting and ad campaign management for your brand.', '$25', 3],
            ['TikTok Service', 'HOT', 'tiktok', 'tiktok', 'Go viral with specialized TikTok algorithm growth packages and content velocity planning.', '$29', 4],
            ['Spotify Service', '', 'spotify', 'spotify', 'Boost streams and monthly listeners with targeted music promotion and playlist outreach.', '$45', 5],
            ['Apple Music', '', 'applemusic', 'apple', 'Premium placement and stream boosting for high-tier music artists and labels.', '$49', 6],
        ],
        'stats' => [
            ['10', 'k+', 'Happy Clients', 'Across 30+ countries', 'users', 1],
            ['95', '%', 'Success Rate', 'Campaigns hitting targets', 'target', 2],
            ['1200', '+', 'Projects Completed', 'Delivered on time', 'check', 3],
            ['15', '+', 'Global Awards', 'Industry recognition', 'award', 4],
        ],
        'steps' => [
            ['Creative Consistency', 'We blend high-quality visuals and compelling storytelling to capture attention and keep your audience engaged across every platform.', 'brush', 1],
            ['Data-Driven Strategy', 'Advanced targeting and optimized paid campaigns ensure your brand message reaches the right audience at the perfect time.', 'chart', 2],
            ['Continuous Analysis', 'Sustained growth comes from studying performance metrics and refining the approach based on real-time user behavior.', 'trend', 3],
            ['Influencer Partnerships', 'Authentic endorsements from trusted creators introduce your brand to highly-engaged new audiences and build instant credibility.', 'megaphone', 4],
        ],
        'features' => [
            ['Creative Purpose', 'Every pixel and word is crafted to strengthen your identity and leave a lasting impression.', 'palette', 1],
            ['Data-Driven Results', 'Advanced analytics monitor and optimize every campaign for maximum ROI.', 'chart', 2],
            ['Meaningful Engagement', 'We spark dialogue, respond to comments and encourage user-generated content that builds authentic community trust.', 'heart', 3],
            ['Continuous Growth', 'A proactive approach keeps your brand ahead in a competitive digital landscape.', 'rocket', 4],
            ['Platform-Safe Methods', 'Compliant, risk-free growth techniques that protect your accounts and reputation.', 'shield', 5],
            ['Dedicated Support', 'A real human growth manager on chat whenever you need strategy or updates.', 'headset', 6],
        ],
        'testimonials' => [
            ['Kevin Wiles', 'Music Producer', '', 'Scaling our international music distribution through Growfy’s strategic digital presence and modern branding was the best decision of our year.', 5, 1],
            ['Johnny Lightning', 'Rock Artist', '', 'They implemented automated song-selling systems and a premium e-commerce experience. Streams up 4x in one quarter.', 5, 2],
            ['Amir Hossain', 'Tech Entrepreneur', '', 'Transformed our digital ecosystem with high-conversion UI/UX and advanced architecture. Absolute professionals.', 5, 3],
            ['Sarah Jenkins', 'Digital Creator', '', 'My social growth finally has a system — targeted content strategy plus data-driven marketing that actually works.', 5, 4],
            ['Marcus Reed', 'CEO, KMC Bazar', '', 'They revolutionized our e-commerce experience with premium optimization. Conversion rate doubled in 60 days.', 5, 5],
        ],
        'faqs' => [
            ['What services does Growfy Agency offer?', 'We provide comprehensive digital solutions including social media marketing, SEO, web development, graphic design and paid ad management (PPC) — tailored to scale your brand.', 1],
            ['How long does it take to see results?', 'Social media engagement typically shows movement within weeks, while SEO and organic growth take 3–6 months. We focus on sustainable long-term success rather than quick temporary fixes.', 2],
            ['Do you provide customized packages?', 'Yes! Every business is unique. We analyze your goals and budget to create a custom roadmap that maximizes your return on investment.', 3],
            ['Which platforms do you specialize in?', 'We are experts in Instagram, YouTube, Facebook, TikTok, Spotify and Apple Music promotion — as well as Google Search and Display networks.', 4],
            ['How do I get started?', 'Simply click “Get Started” or “Contact Us”. We’ll schedule a free consultation to discuss your vision and how Growfy can bring it to life.', 5],
        ],
    ];
}
