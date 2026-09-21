{{-- ===================================================================
     Punjab Lottery Seller - Landing Page (English + ਪੰਜਾਬੀ)
     Default language: English. User toggle karega to localStorage me save hoga.

     Optional keys $landing me (agar ho to Punjabi me dikhengi, warna English hi dikhegi):
     title_pa, kicker_pa, subtitle_pa, bumper_title_pa, bumper_prize_pa,
     result_text_pa, footer_text_pa, address_pa
==================================================================== --}}
@php
    // Dono language ek saath render hoti hain, CSS se ek chhupti hai (page reload nahi lagta)
    $t = function ($en, $pa = null) {
        return new \Illuminate\Support\HtmlString(
            '<span data-l="en">'.e($en).'</span><span data-l="pa">'.e($pa ?? $en).'</span>'
        );
    };

    // WhatsApp / Call links
    $waNumber = preg_replace('/\D+/', '', $landing['whatsapp'] ?? '');
    $waLink   = 'https://wa.me/'.$waNumber;
    $waText   = fn ($msg) => $waLink.'?text='.rawurlencode($msg);
    $callHref = 'tel:'.preg_replace('/\s+/', '', $landing['call'] ?? '');

    // Punjabi months (draw date ke liye)
    $paMonths = [1=>'ਜਨਵਰੀ',2=>'ਫ਼ਰਵਰੀ',3=>'ਮਾਰਚ',4=>'ਅਪ੍ਰੈਲ',5=>'ਮਈ',6=>'ਜੂਨ',7=>'ਜੁਲਾਈ',8=>'ਅਗਸਤ',9=>'ਸਤੰਬਰ',10=>'ਅਕਤੂਬਰ',11=>'ਨਵੰਬਰ',12=>'ਦਸੰਬਰ'];

    // Bumper date safe parse - date galat/blank ho to page crash nahi hoga
    $bumperDate = null;
    try {
        $bumperDate = !empty($landing['bumper_date']) ? \Illuminate\Support\Carbon::parse($landing['bumper_date']) : null;
    } catch (\Throwable $e) {
        $bumperDate = null;
    }
    $bumperDateEn = $bumperDate?->format('d F Y');
    $bumperDatePa = $bumperDate ? $bumperDate->format('d').' '.$paMonths[$bumperDate->month].' '.$bumperDate->format('Y') : null;
    $bumperTime   = $bumperDate?->format('h:i A');

    // Ticket frequency labels
    $freqPa   = ['daily'=>'ਰੋਜ਼ਾਨਾ','weekly'=>'ਹਫ਼ਤਾਵਾਰੀ','monthly'=>'ਮਹੀਨਾਵਾਰ','festival'=>'ਤਿਉਹਾਰ'];
    $freqIcon = ['daily'=>'🎟','weekly'=>'🎫','monthly'=>'🗓','festival'=>'🎉'];

    // Process steps: [icon, chip EN, chip PA, title EN, title PA, text EN, text PA, extra class]
    $stepsBefore = [
        ['fab fa-whatsapp', 'WhatsApp', 'ਵਟਸਐਪ',
         'Buy your ticket on WhatsApp', 'ਵਟਸਐਪ ਉੱਤੇ ਟਿਕਟ ਖਰੀਦੋ',
         'Message us on WhatsApp, choose your ticket type and quantity, and confirm your purchase with the seller.',
         'ਸਾਨੂੰ ਵਟਸਐਪ ਉੱਤੇ ਸੁਨੇਹਾ ਭੇਜੋ, ਟਿਕਟ ਦੀ ਕਿਸਮ ਅਤੇ ਗਿਣਤੀ ਚੁਣੋ ਅਤੇ ਵਿਕਰੇਤਾ ਨਾਲ ਖਰੀਦ ਪੱਕੀ ਕਰੋ।', ''],
        ['fas fa-sign-in-alt', 'Panel', 'ਪੈਨਲ',
         'Login to your panel', 'ਆਪਣੇ ਪੈਨਲ ਵਿੱਚ ਲੌਗਇਨ ਕਰੋ',
         'Use the Login button with your registered email and password to enter your own panel.',
         'ਲੌਗਇਨ ਬਟਨ ਦਬਾ ਕੇ ਆਪਣੀ ਰਜਿਸਟਰਡ ਈਮੇਲ ਅਤੇ ਪਾਸਵਰਡ ਨਾਲ ਆਪਣੇ ਪੈਨਲ ਵਿੱਚ ਜਾਓ।', ''],
        ['fas fa-ticket-alt', 'My tickets', 'ਮੇਰੀਆਂ ਟਿਕਟਾਂ',
         'See your purchased tickets', 'ਖਰੀਦੀਆਂ ਟਿਕਟਾਂ ਵੇਖੋ',
         'Every ticket you bought shows up in your panel with its number, draw and status.',
         'ਤੁਹਾਡੀ ਖਰੀਦੀ ਹਰ ਟਿਕਟ ਨੰਬਰ, ਡਰਾਅ ਅਤੇ ਸਥਿਤੀ ਸਮੇਤ ਤੁਹਾਡੇ ਪੈਨਲ ਵਿੱਚ ਦਿਖਾਈ ਦਿੰਦੀ ਹੈ।', ''],
    ];
    $stepsAfter = [
        ['fas fa-trophy', 'Result', 'ਨਤੀਜਾ',
         'Your ticket wins', 'ਤੁਹਾਡੀ ਟਿਕਟ ਜਿੱਤਦੀ ਹੈ',
         'When one of your tickets wins, the prize position and winning amount are shown in your panel and the withdrawal option appears.',
         'ਜਦੋਂ ਤੁਹਾਡੀ ਕੋਈ ਟਿਕਟ ਜਿੱਤਦੀ ਹੈ, ਤਾਂ ਇਨਾਮ ਦੀ ਪੋਜ਼ੀਸ਼ਨ ਅਤੇ ਜਿੱਤੀ ਰਕਮ ਪੈਨਲ ਵਿੱਚ ਦਿਖਾਈ ਦਿੰਦੀ ਹੈ ਅਤੇ ਪੈਸੇ ਕਢਵਾਉਣ ਦਾ ਵਿਕਲਪ ਆ ਜਾਂਦਾ ਹੈ।', ''],
        ['fas fa-id-card', 'KYC', 'ਕੇਵਾਈਸੀ',
         'Complete KYC first', 'ਪਹਿਲਾਂ ਕੇਵਾਈਸੀ ਪੂਰੀ ਕਰੋ',
         'Before you withdraw, add your bank account details and upload your KYC documents.',
         'ਪੈਸੇ ਕਢਵਾਉਣ ਤੋਂ ਪਹਿਲਾਂ ਆਪਣੇ ਬੈਂਕ ਖਾਤੇ ਦੇ ਵੇਰਵੇ ਭਰੋ ਅਤੇ ਕੇਵਾਈਸੀ ਦਸਤਾਵੇਜ਼ ਅਪਲੋਡ ਕਰੋ।', 'key'],
        ['fas fa-hand-holding-usd', 'Withdraw', 'ਕਢਵਾਓ',
         'Withdraw your winnings', 'ਆਪਣੀ ਜਿੱਤੀ ਰਕਮ ਕਢਵਾਓ',
         'Submit the withdrawal request and track it in your panel. Any charge, if it applies, is shown there along with the net amount you receive.',
         'ਕਢਵਾਉਣ ਦੀ ਬੇਨਤੀ ਭੇਜੋ ਅਤੇ ਪੈਨਲ ਵਿੱਚ ਉਸਦੀ ਸਥਿਤੀ ਵੇਖੋ। ਜੇ ਕੋਈ ਚਾਰਜ ਲੱਗਦਾ ਹੈ ਤਾਂ ਉਹ ਤੁਹਾਨੂੰ ਮਿਲਣ ਵਾਲੀ ਸ਼ੁੱਧ ਰਕਮ ਸਮੇਤ ਉੱਥੇ ਹੀ ਦਿਖਾਈ ਦਿੰਦਾ ਹੈ।', ''],
    ];

    // Bumper calendar: [icon, name EN, name PA, when EN, when PA, text EN, text PA]
    $calendar = [
        ['fa-seedling', 'Baisakhi Bumper', 'ਵਿਸਾਖੀ ਬੰਪਰ', 'Every April', 'ਹਰ ਅਪ੍ਰੈਲ', 'Punjab spring bumper draw.', 'ਪੰਜਾਬ ਦਾ ਬਸੰਤ ਰੁੱਤ ਦਾ ਬੰਪਰ ਡਰਾਅ।'],
        ['fa-hand-holding-heart', 'Rakhi Bumper', 'ਰੱਖੜੀ ਬੰਪਰ', 'Every August', 'ਹਰ ਅਗਸਤ', 'Seasonal Rakhi bumper tickets.', 'ਰੱਖੜੀ ਦੇ ਮੌਸਮ ਦੀਆਂ ਬੰਪਰ ਟਿਕਟਾਂ।'],
        ['fa-fire', 'Lohri Bumper', 'ਲੋਹੜੀ ਬੰਪਰ', 'Every January', 'ਹਰ ਜਨਵਰੀ', 'Winter festival draw.', 'ਸਰਦੀ ਦੇ ਤਿਉਹਾਰ ਦਾ ਡਰਾਅ।'],
        ['fa-city', 'Diwali Bumper', 'ਦੀਵਾਲੀ ਬੰਪਰ', 'Every November', 'ਹਰ ਨਵੰਬਰ', 'Festival season special.', 'ਤਿਉਹਾਰਾਂ ਦੇ ਮੌਸਮ ਦਾ ਖ਼ਾਸ ਡਰਾਅ।'],
        ['fa-star', 'Christmas & New Year Bumper', 'ਕ੍ਰਿਸਮਸ ਅਤੇ ਨਵਾਂ ਸਾਲ ਬੰਪਰ', 'Dec - Jan', 'ਦਸੰਬਰ - ਜਨਵਰੀ', 'Year-end bumper season.', 'ਸਾਲ ਦੇ ਅੰਤ ਦਾ ਬੰਪਰ ਮੌਸਮ।'],
    ];

    // FAQ: [Q EN, Q PA, A EN, A PA]
    $faqs = [
        ['How do I buy a ticket?', 'ਮੈਂ ਟਿਕਟ ਕਿਵੇਂ ਖਰੀਦਾਂ?',
         'Send us a message on WhatsApp with the ticket type and quantity you want. Once your purchase is confirmed, your tickets appear in your panel.',
         'ਸਾਨੂੰ ਵਟਸਐਪ ਉੱਤੇ ਆਪਣੀ ਲੋੜੀਂਦੀ ਟਿਕਟ ਦੀ ਕਿਸਮ ਅਤੇ ਗਿਣਤੀ ਦੱਸੋ। ਖਰੀਦ ਪੱਕੀ ਹੋਣ ਤੋਂ ਬਾਅਦ ਤੁਹਾਡੀਆਂ ਟਿਕਟਾਂ ਪੈਨਲ ਵਿੱਚ ਦਿਖਾਈ ਦਿੰਦੀਆਂ ਹਨ।'],
        ['Where can I see my tickets and results?', 'ਮੈਂ ਆਪਣੀਆਂ ਟਿਕਟਾਂ ਅਤੇ ਨਤੀਜੇ ਕਿੱਥੇ ਵੇਖਾਂ?',
         'Login to your panel. Your purchased tickets, draw results and winning tickets are all shown there.',
         'ਆਪਣੇ ਪੈਨਲ ਵਿੱਚ ਲੌਗਇਨ ਕਰੋ। ਖਰੀਦੀਆਂ ਟਿਕਟਾਂ, ਡਰਾਅ ਦੇ ਨਤੀਜੇ ਅਤੇ ਜੇਤੂ ਟਿਕਟਾਂ ਸਭ ਉੱਥੇ ਦਿਖਾਈਆਂ ਜਾਂਦੀਆਂ ਹਨ।'],
        ['What do I need to withdraw my winnings?', 'ਜਿੱਤੀ ਰਕਮ ਕਢਵਾਉਣ ਲਈ ਕੀ ਚਾਹੀਦਾ ਹੈ?',
         'You must complete KYC first: your bank account details and KYC documents. After that you can submit the withdrawal request.',
         'ਪਹਿਲਾਂ ਕੇਵਾਈਸੀ ਪੂਰੀ ਕਰਨੀ ਪਵੇਗੀ: ਬੈਂਕ ਖਾਤੇ ਦੇ ਵੇਰਵੇ ਅਤੇ ਕੇਵਾਈਸੀ ਦਸਤਾਵੇਜ਼। ਉਸ ਤੋਂ ਬਾਅਦ ਤੁਸੀਂ ਕਢਵਾਉਣ ਦੀ ਬੇਨਤੀ ਭੇਜ ਸਕਦੇ ਹੋ।'],
        ['Is there any charge, and who do I pay?', 'ਕੀ ਕੋਈ ਚਾਰਜ ਲੱਗਦਾ ਹੈ, ਅਤੇ ਭੁਗਤਾਨ ਕਿਸਨੂੰ ਕਰਾਂ?',
         'If a charge applies, it is displayed in your panel. Pay online only when your panel shows it, never because someone asked you on a call or message.',
         'ਜੇ ਕੋਈ ਚਾਰਜ ਲੱਗਦਾ ਹੈ ਤਾਂ ਉਹ ਤੁਹਾਡੇ ਪੈਨਲ ਵਿੱਚ ਦਿਖਾਇਆ ਜਾਂਦਾ ਹੈ। ਭੁਗਤਾਨ ਸਿਰਫ਼ ਤਦ ਹੀ ਆਨਲਾਈਨ ਕਰੋ ਜਦੋਂ ਪੈਨਲ ਵਿੱਚ ਦਿਖੇ, ਫ਼ੋਨ ਜਾਂ ਸੁਨੇਹੇ ਉੱਤੇ ਕਿਸੇ ਦੇ ਕਹਿਣ ਉੱਤੇ ਨਹੀਂ।'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en" data-lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $landing['title'] ?? 'Punjab Lottery Portal' }}</title>
    <meta name="description" content="{{ $landing['subtitle'] ?? '' }}">

    {{-- Language pehle hi set kar do taaki page load par flash na ho --}}
    <script>
        (function () {
            try {
                var l = localStorage.getItem('pl_lang');
                if (l === 'pa' || l === 'en') {
                    document.documentElement.setAttribute('data-lang', l);
                    document.documentElement.setAttribute('lang', l);
                }
            } catch (e) {}
        })();
    </script>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800;900&family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Gurmukhi:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ===============================================================
           Design tokens - pl- prefix taaki koi framework clash na kare
        ================================================================ */
        :root{
            --pl-maroon:#7A0E24;
            --pl-maroon-deep:#4A0715;
            --pl-maroon-soft:rgba(122,14,36,.07);
            --pl-gold:#D4AF37;
            --pl-gold-light:#F3D57E;
            --pl-gold-dark:#8A6A00;
            --pl-gold-gradient:linear-gradient(135deg,#F6DA8B 0%,#D4AF37 55%,#B8860B 100%);
            --pl-charcoal:#100B0D;
            --pl-ink:#1E1417;
            --pl-muted:#655B60;
            --pl-cream:#FBF7EF;
            --pl-sand:#F5EFE4;
            --pl-line:rgba(122,14,36,.13);
            --pl-shadow:0 24px 60px -24px rgba(74,7,21,.30);
        }
        *{box-sizing:border-box;margin:0;padding:0;}
        html{scroll-behavior:smooth;}
        section[id]{scroll-margin-top:90px;}
        body{
            font-family:'Inter','Noto Sans Gurmukhi',Arial,sans-serif;
            color:var(--pl-ink);
            background:var(--pl-cream);
            -webkit-font-smoothing:antialiased;
            line-height:1.5;
        }
        h1,h2,h3,h4{ font-family:'Outfit','Noto Sans Gurmukhi',sans-serif; letter-spacing:-.01em; }
        a{ text-decoration:none; color:inherit; }
        img{ max-width:100%; display:block; }
        .container{ max-width:1240px; margin:0 auto; padding:0 28px; }
        :focus-visible{ outline:3px solid var(--pl-gold); outline-offset:3px; }

        /* ===== Language switch: dono language DOM me hain, ek chhupi rehti hai ===== */
        [data-l]{ display:none; }
        html[data-lang="en"] [data-l="en"],
        html[data-lang="pa"] [data-l="pa"]{ display:inline; }
        /* Gurmukhi me letter-spacing/line-height alag chahiye */
        html[data-lang="pa"] h1,html[data-lang="pa"] h2,html[data-lang="pa"] h3,html[data-lang="pa"] h4{ letter-spacing:0; }
        html[data-lang="pa"] .pl-hero h1{ line-height:1.32; font-size:50px; }
        html[data-lang="pa"] .pl-section h2{ line-height:1.35; }
        html[data-lang="pa"] p, html[data-lang="pa"] li{ line-height:1.8; }

        /* ===== Buttons ===== */
        .pl-btn{
            display:inline-flex; align-items:center; justify-content:center; gap:9px;
            border-radius:999px; font-weight:700; font-size:14.5px;
            padding:13px 24px; border:0; cursor:pointer; transition:transform .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .pl-btn-gold{ background:var(--pl-gold-gradient); color:var(--pl-maroon-deep); box-shadow:0 10px 26px -8px rgba(212,175,55,.6); }
        .pl-btn-gold:hover{ transform:translateY(-2px); box-shadow:0 14px 32px -8px rgba(212,175,55,.7); }
        .pl-btn-outline{ background:#fff; color:var(--pl-maroon); border:1.5px solid var(--pl-line); }
        .pl-btn-outline:hover{ background:var(--pl-maroon-soft); }
        .pl-btn-wa{ background:#1FB855; color:#fff; box-shadow:0 12px 28px -10px rgba(31,184,85,.6); }
        .pl-btn-wa:hover{ transform:translateY(-2px); background:#19a34b; }
        .pl-btn-dark{ background:var(--pl-charcoal); color:#fff; }
        .pl-btn-sm{ padding:10px 18px; font-size:13.5px; }

        /* ===== Nav ===== */
        .pl-nav{
            position:sticky; top:0; z-index:30;
            background:rgba(251,247,239,.86);
            backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);
            border-bottom:1px solid var(--pl-line);
        }
        .pl-nav .row{ min-height:76px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .pl-brand{ display:flex; align-items:center; gap:11px; font-family:'Outfit',sans-serif; font-weight:800; font-size:19px; letter-spacing:-.01em; }
        .pl-mark{
            width:40px; height:40px; border-radius:12px; background:var(--pl-gold-gradient);
            display:flex; align-items:center; justify-content:center; color:var(--pl-maroon-deep); font-size:17px;
            box-shadow:0 8px 18px -6px rgba(212,175,55,.55);
        }
        .pl-brand-logo{
            width:42px; height:42px; border-radius:10px; object-fit:contain; background:#fff;
            border:1px solid var(--pl-line); padding:4px; box-shadow:0 8px 18px -8px rgba(74,7,21,.24);
        }
        .pl-nav-links{ display:flex; gap:26px; font-size:14.5px; font-weight:600; color:var(--pl-muted); }
        .pl-nav-links a:hover{ color:var(--pl-maroon); }
        .pl-nav-actions{ display:flex; align-items:center; gap:12px; }
        .pl-lang-switch{ display:inline-flex; background:#fff; border:1.5px solid var(--pl-line); border-radius:999px; padding:3px; }
        .pl-lang-switch button{
            border:0; background:transparent; cursor:pointer; color:var(--pl-muted);
            font:700 13px 'Inter','Noto Sans Gurmukhi',sans-serif; padding:7px 14px; border-radius:999px; transition:.18s ease;
        }
        html[data-lang="en"] .pl-lang-switch [data-set="en"],
        html[data-lang="pa"] .pl-lang-switch [data-set="pa"]{ background:var(--pl-maroon); color:#fff; }

        /* ===== Hero ===== */
        .pl-hero{ position:relative; padding:78px 0 96px; overflow:hidden; }
        .pl-hero::before{
            content:''; position:absolute; inset:0; pointer-events:none;
            background:
                radial-gradient(620px 340px at 8% -10%, rgba(212,175,55,.18), transparent 60%),
                radial-gradient(560px 320px at 96% 20%, rgba(122,14,36,.10), transparent 60%);
        }
        .pl-hero-grid{ position:relative; display:grid; grid-template-columns:1.05fr .95fr; gap:56px; align-items:center; }
        .pl-kicker{
            display:inline-flex; align-items:center; gap:8px; font-size:14px; font-weight:700;
            color:var(--pl-maroon); background:var(--pl-maroon-soft); border:1px solid var(--pl-line);
            padding:8px 16px; border-radius:999px;
        }
        .pl-hero h1{ font-size:58px; line-height:1.08; font-weight:800; margin:22px 0 20px; color:var(--pl-ink); }
        .pl-hero .lead{ font-size:19px; color:var(--pl-muted); max-width:560px; line-height:1.65; }
        .pl-chips{ display:flex; flex-wrap:wrap; gap:8px; margin:26px 0 30px; }
        .pl-chip{ font-size:13.5px; font-weight:600; padding:7px 14px; border-radius:999px; background:#fff; border:1px solid var(--pl-line); color:var(--pl-maroon); }
        .pl-cta-row{ display:flex; gap:14px; flex-wrap:wrap; }
        .pl-perks{ display:flex; gap:12px 26px; flex-wrap:wrap; margin-top:30px; color:var(--pl-muted); font-size:14.5px; font-weight:600; }
        .pl-perks i{ color:#1FB855; margin-right:7px; }

        /* Hero ticket visual - yahi page ka ek yaadgaar element hai */
        .pl-visual{ position:relative; display:flex; justify-content:center; padding:26px 10px 34px; }
        .pl-mock{
            position:relative; width:100%; max-width:410px; border-radius:26px; overflow:hidden; color:#fff;
            background:linear-gradient(150deg,var(--pl-maroon-deep) 0%,var(--pl-maroon) 100%);
            box-shadow:0 40px 80px -28px rgba(74,7,21,.65);
            transform:rotate(-3deg); animation:plSettle .9s cubic-bezier(.2,.8,.2,1) both;
        }
        @keyframes plSettle{ from{ transform:rotate(-9deg) translateY(28px); opacity:0; } to{ transform:rotate(-3deg) translateY(0); opacity:1; } }
        .pl-mock::before{
            content:''; position:absolute; right:-70px; top:-70px; width:240px; height:240px; border-radius:50%;
            background:radial-gradient(circle,rgba(243,213,126,.42),transparent 65%);
        }
        .pl-mock-top{ position:relative; padding:28px 28px 26px; }
        .pl-mock-top .brand{ font-size:13px; font-weight:700; color:var(--pl-gold-light); }
        .pl-mock-top h3{
            font-size:44px; line-height:1.05; margin:10px 0 18px; font-weight:800;
            background:var(--pl-gold-gradient); -webkit-background-clip:text; background-clip:text; color:transparent;
        }
        .pl-mock-num{ font-family:'Outfit',sans-serif; font-size:30px; font-weight:700; letter-spacing:.14em; }
        .pl-mock-num small{ display:block; font-family:'Inter','Noto Sans Gurmukhi',sans-serif; font-size:12px; font-weight:500; letter-spacing:0; color:rgba(255,255,255,.62); margin-bottom:4px; }
        .pl-mock-stub{
            position:relative; padding:18px 28px 22px; border-top:2px dashed rgba(255,255,255,.3);
            display:flex; justify-content:space-between; align-items:center; font-size:13px; color:rgba(255,255,255,.78);
        }
        .pl-mock-stub::before,.pl-mock-stub::after{ content:''; position:absolute; top:-13px; width:24px; height:24px; border-radius:50%; background:var(--pl-cream); }
        .pl-mock-stub::before{ left:-12px; }
        .pl-mock-stub::after{ right:-12px; }
        .pl-mock-stub strong{ font-family:'Outfit',sans-serif; font-size:20px; color:var(--pl-gold-light); }
        .pl-note-chip{
            position:absolute; display:flex; gap:11px; align-items:center; max-width:250px;
            background:#fff; border:1px solid var(--pl-line); border-radius:14px; padding:11px 14px;
            font-size:13px; font-weight:600; line-height:1.4; box-shadow:0 18px 36px -16px rgba(74,7,21,.35);
        }
        .pl-note-chip .ic{ flex:none; width:32px; height:32px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:15px; }
        .pl-note-chip.a{ top:0; right:0; }
        .pl-note-chip.a .ic{ background:var(--pl-gold-gradient); color:var(--pl-maroon-deep); }
        .pl-note-chip.b{ bottom:0; left:0; }
        .pl-note-chip.b .ic{ background:var(--pl-maroon); color:#fff; }

        /* ===== Bumper / countdown ===== */
        .pl-bumper{
            position:relative; overflow:hidden; color:#fff; text-align:center; padding:68px 0;
            background:linear-gradient(160deg,var(--pl-maroon-deep) 0%,var(--pl-maroon) 100%);
        }
        .pl-bumper::before{ content:''; position:absolute; inset:0; pointer-events:none; background:radial-gradient(520px 260px at 80% -20%, rgba(212,175,55,.24), transparent 60%); }
        .pl-bumper .eyebrow{ position:relative; color:var(--pl-gold-light); font-weight:700; font-size:15px; }
        .pl-bumper h2{ position:relative; font-size:42px; margin:14px 0 6px; font-weight:800; }
        .pl-timer{ position:relative; display:flex; justify-content:center; gap:14px; margin:26px 0 22px; flex-wrap:wrap; }
        .pl-timebox{ background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.16); border-radius:14px; padding:14px 22px; min-width:92px; }
        .pl-timebox strong{
            display:block; font-family:'Outfit',sans-serif; font-size:34px; font-weight:800; line-height:1.2;
            background:var(--pl-gold-gradient); -webkit-background-clip:text; background-clip:text; color:transparent;
        }
        .pl-timebox .lbl{ display:block; font-style:normal; color:rgba(255,255,255,.7); font-size:13px; font-weight:600; }
        .pl-timer-note{ position:relative; color:var(--pl-gold-light); font-weight:700; margin-bottom:18px; }
        .pl-timer-note[hidden]{ display:none; }
        .pl-bumper .fine{ position:relative; color:rgba(255,255,255,.66); font-size:13.5px; max-width:600px; margin:0 auto; }

        /* ===== Generic sections ===== */
        .pl-section{ padding:96px 0; text-align:center; }
        .pl-section.grey{ background:var(--pl-sand); }
        .pl-head h2{ font-size:44px; line-height:1.16; margin:0 0 14px; font-weight:800; }
        .pl-head p{ font-size:18.5px; line-height:1.7; color:var(--pl-muted); max-width:660px; margin:0 auto; }

        /* ===== Ticket cards ===== */
        .pl-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:26px; margin-top:48px; text-align:left; }
        .pl-ticket{
            position:relative; display:flex; flex-direction:column; overflow:hidden;
            background:#fff; border:1px solid var(--pl-line); border-radius:22px; box-shadow:var(--pl-shadow);
            transition:transform .18s ease, box-shadow .18s ease;
        }
        .pl-ticket:hover{ transform:translateY(-5px); box-shadow:0 32px 70px -24px rgba(74,7,21,.36); }
        .pl-ticket-body{ position:relative; flex:1; padding:30px 28px 26px; }
        .pl-ribbon{
            display:inline-block; background:var(--pl-gold-gradient); color:var(--pl-maroon-deep);
            font-size:12.5px; font-weight:700; padding:5px 13px; border-radius:999px; margin-bottom:16px;
        }
        .pl-ticket-img{ width:100%; aspect-ratio:16/10; object-fit:cover; border-radius:12px; margin-bottom:16px; }
        .pl-ticket .icon{ font-size:40px; margin-bottom:12px; }
        .pl-ticket h3{ font-size:23px; margin-bottom:8px; font-weight:700; }
        .pl-ticket p{ font-size:15.5px; color:var(--pl-muted); line-height:1.6; }
        .pl-ticket-stub{
            position:relative; display:flex; justify-content:space-between; align-items:center; gap:12px;
            padding:18px 28px 22px; border-top:2px dashed var(--pl-line); background:#FFFBF2;
        }
        .pl-ticket-stub::before,.pl-ticket-stub::after{
            content:''; position:absolute; top:-12px; width:22px; height:22px; border-radius:50%;
            background:var(--pl-cream); border:1px solid var(--pl-line);
        }
        .pl-ticket-stub::before{ left:-12px; }
        .pl-ticket-stub::after{ right:-12px; }
        .pl-price small{ display:block; font-size:12.5px; color:var(--pl-muted); }
        .pl-price strong{ font-family:'Outfit',sans-serif; font-size:26px; font-weight:800; color:var(--pl-maroon); line-height:1.1; }

        /* ===== How it works ===== */
        .pl-phase{ display:flex; align-items:center; gap:14px; margin:52px 0 22px; text-align:left; }
        .pl-phase h3{ font-size:22px; font-weight:700; white-space:nowrap; }
        .pl-phase .tag{ background:var(--pl-maroon); color:#fff; font-size:13px; font-weight:700; padding:6px 14px; border-radius:999px; white-space:nowrap; }
        .pl-phase::after{ content:''; flex:1; height:1px; background:var(--pl-line); }
        .pl-steps{ display:grid; grid-template-columns:repeat(3,1fr); gap:22px; text-align:left; }
        .pl-step{
            position:relative; background:#fff; border:1px solid var(--pl-line); border-radius:18px;
            padding:30px 26px 28px; overflow:hidden;
        }
        .pl-step .num{
            position:absolute; right:20px; top:8px; font-family:'Outfit',sans-serif; font-weight:800;
            font-size:80px; line-height:1; color:var(--pl-maroon-soft); pointer-events:none;
        }
        .pl-step .ico{
            width:54px; height:54px; border-radius:16px; margin-bottom:18px; font-size:22px;
            background:var(--pl-gold-gradient); color:var(--pl-maroon-deep);
            display:flex; align-items:center; justify-content:center; box-shadow:0 10px 22px -10px rgba(212,175,55,.7);
        }
        .pl-step-chip{ display:inline-block; font-size:12.5px; font-weight:700; color:var(--pl-maroon); background:var(--pl-maroon-soft); padding:4px 11px; border-radius:999px; margin-bottom:10px; }
        .pl-step h3{ font-size:21px; font-weight:700; margin-bottom:8px; position:relative; }
        .pl-step p{ font-size:15.5px; color:var(--pl-muted); line-height:1.65; position:relative; }
        .pl-step.key{ border:1.5px solid var(--pl-gold); box-shadow:0 22px 50px -28px rgba(212,175,55,.8); }

        /* ===== Charges / safety ===== */
        .pl-safe{ background:radial-gradient(700px 360px at 90% 0%, rgba(212,175,55,.14), transparent 60%), var(--pl-charcoal); color:#fff; padding:100px 0; }
        .pl-safe-grid{ display:grid; grid-template-columns:1fr 1fr; gap:64px; align-items:center; text-align:left; }
        .pl-safe h2{ font-size:42px; line-height:1.18; font-weight:800; margin-bottom:16px; }
        .pl-safe .lead{ font-size:18px; line-height:1.7; color:rgba(255,255,255,.74); }
        .pl-rules{ list-style:none; margin-top:28px; display:grid; gap:12px; }
        .pl-rules li{
            display:flex; gap:14px; align-items:flex-start; padding:14px 16px; border-radius:14px;
            background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09); font-size:15.5px; line-height:1.55;
        }
        .pl-rules .mk{ flex:none; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:13px; margin-top:1px; }
        .pl-rules .mk.ok{ background:rgba(34,197,94,.18); color:#4ade80; }
        .pl-rules .mk.no{ background:rgba(239,68,68,.18); color:#f87171; }
        .pl-panel{ background:#fff; color:var(--pl-ink); border-radius:20px; overflow:hidden; box-shadow:0 44px 90px -30px rgba(0,0,0,.7); }
        .pl-panel-bar{ display:flex; align-items:center; gap:7px; padding:14px 18px; background:var(--pl-sand); border-bottom:1px solid var(--pl-line); }
        .pl-panel-bar .dot{ width:10px; height:10px; border-radius:50%; background:#E2D5BC; }
        .pl-panel-bar .url{ margin-left:10px; font-size:12.5px; color:var(--pl-muted); background:#fff; border:1px solid var(--pl-line); padding:4px 14px; border-radius:999px; }
        .pl-panel-body{ padding:24px 26px 26px; }
        .pl-panel-title{ display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:8px; }
        .pl-panel-title h3{ font-size:19px; font-weight:700; }
        .pl-badge{ font-size:12.5px; font-weight:700; padding:5px 12px; border-radius:999px; background:rgba(212,175,55,.22); color:var(--pl-gold-dark); }
        .pl-row{ display:flex; justify-content:space-between; align-items:center; gap:12px; padding:15px 0; border-bottom:1px dashed var(--pl-line); font-size:15px; }
        .pl-row b{ font-family:'Outfit',sans-serif; font-size:19px; letter-spacing:.08em; color:var(--pl-muted); }
        .pl-row.net{ font-weight:700; }
        .pl-row.net b{ color:var(--pl-maroon); }
        .pl-track{ display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-top:22px; }
        .pl-track div{ text-align:center; font-size:12.5px; font-weight:600; color:var(--pl-muted); }
        .pl-track div::before{ content:''; display:block; height:6px; border-radius:99px; background:#EADFCC; margin-bottom:8px; }
        .pl-track div.on{ color:var(--pl-maroon); }
        .pl-track div.on::before{ background:var(--pl-gold-gradient); }
        .pl-panel-note{ margin-top:20px; padding:13px 15px; border-radius:12px; background:var(--pl-maroon-soft); color:var(--pl-maroon); font-size:13.5px; font-weight:600; display:flex; gap:10px; line-height:1.5; }
        .pl-panel-cap{ text-align:center; font-size:12.5px; color:rgba(255,255,255,.5); margin-top:14px; }

        /* ===== Bumper calendar ===== */
        .pl-calendar{ display:grid; grid-template-columns:repeat(5,1fr); gap:16px; margin-top:52px; }
        .pl-cal-item{ background:#fff; border:1px solid var(--pl-line); border-radius:16px; padding:30px 18px; text-align:center; transition:border-color .18s ease, box-shadow .18s ease; }
        .pl-cal-item:hover{ border-color:var(--pl-gold); box-shadow:0 16px 40px -18px rgba(212,175,55,.45); }
        .pl-cal-item .ico{ width:52px; height:52px; margin:0 auto 14px; border-radius:50%; background:var(--pl-maroon-soft); color:var(--pl-maroon); display:flex; align-items:center; justify-content:center; font-size:21px; }
        .pl-cal-item h3{ font-size:19px; margin-bottom:6px; font-weight:700; }
        .pl-cal-item strong{ color:var(--pl-maroon); display:block; font-size:14px; margin-bottom:8px; }
        .pl-cal-item p{ font-size:14px; color:var(--pl-muted); line-height:1.55; }

        /* ===== Result ===== */
        .pl-section.dark{ background:var(--pl-charcoal); color:#fff; text-align:left; }
        .pl-result-wrap{ display:grid; grid-template-columns:1.1fr 1fr; gap:64px; align-items:center; }
        .pl-result-wrap h2{ font-size:42px; line-height:1.18; font-weight:800; margin-bottom:16px; }
        .pl-result-wrap p{ font-size:18px; line-height:1.7; color:rgba(255,255,255,.72); }
        .pl-result-wrap .hint{ margin-top:18px; display:flex; gap:10px; align-items:center; font-size:15px; font-weight:600; color:var(--pl-gold-light); }
        .pl-result-panel{ background:var(--pl-gold-gradient); color:var(--pl-maroon-deep); border-radius:22px; padding:46px; text-align:center; box-shadow:0 30px 70px -20px rgba(212,175,55,.38); }
        .pl-result-panel .play-ico{ width:64px; height:64px; border-radius:50%; background:rgba(0,0,0,.12); display:flex; align-items:center; justify-content:center; font-size:22px; margin:0 auto 18px; }
        .pl-result-panel h3{ font-size:26px; margin-bottom:12px; font-weight:800; }
        .pl-result-panel p{ font-size:15.5px; margin-bottom:22px; color:var(--pl-maroon-deep); opacity:.85; }

        /* ===== Trust ===== */
        .pl-trust{ background:linear-gradient(160deg,#F5EFE4 0%,#EFE4D0 100%); border-radius:26px; padding:72px 30px; text-align:center; }
        .pl-trust-row{ display:grid; grid-template-columns:repeat(4,1fr); gap:22px; margin-top:44px; }
        .pl-trust-item{ background:#fff; border:1px solid var(--pl-line); border-radius:16px; padding:28px 18px; font-size:16px; font-weight:700; }
        .pl-trust-item .ring{ width:56px; height:56px; margin:0 auto 16px; border-radius:50%; background:var(--pl-gold-gradient); display:flex; align-items:center; justify-content:center; font-size:24px; box-shadow:0 10px 24px -10px rgba(212,175,55,.55); }

        /* ===== FAQ ===== */
        .pl-faq{ max-width:840px; margin:44px auto 0; display:grid; gap:12px; text-align:left; }
        .pl-faq details{ background:#fff; border:1px solid var(--pl-line); border-radius:14px; padding:0 22px; }
        .pl-faq details[open]{ border-color:var(--pl-gold); box-shadow:0 18px 40px -26px rgba(212,175,55,.8); }
        .pl-faq summary{ cursor:pointer; list-style:none; padding:20px 0; font-weight:700; font-size:16.5px; display:flex; justify-content:space-between; align-items:center; gap:16px; }
        .pl-faq summary::-webkit-details-marker{ display:none; }
        .pl-faq summary i{ color:var(--pl-maroon); font-size:14px; transition:transform .2s ease; }
        .pl-faq details[open] summary i{ transform:rotate(180deg); }
        .pl-faq p{ padding:0 0 20px; color:var(--pl-muted); line-height:1.7; font-size:15.5px; }

        /* ===== Footer ===== */
        .pl-footer{ background:var(--pl-charcoal); color:#fff; padding:80px 0 30px; }
        .pl-footer-grid{ display:grid; grid-template-columns:1fr 1fr 1.4fr; gap:64px; }
        .pl-footer-grid h4{ color:rgba(255,255,255,.55); font-size:14px; margin-bottom:14px; font-weight:600; }
        .pl-footer-grid h3{ font-size:26px; line-height:1.55; font-weight:700; }
        .pl-footer-grid p{ color:rgba(255,255,255,.78); font-size:15.5px; line-height:1.8; }
        .pl-footer-grid .gold{ color:var(--pl-gold-light); }
        .pl-footer-grid .mt{ margin-top:22px; }
        .pl-copyright{ text-align:center; color:rgba(255,255,255,.45); font-size:13px; margin-top:60px; padding-top:26px; border-top:1px solid rgba(255,255,255,.08); }

        /* ===== Floating actions ===== */
        .pl-float-actions{ position:fixed; right:24px; bottom:24px; z-index:20; display:flex; flex-direction:column; gap:12px; }
        .pl-float-btn{ width:58px; height:58px; border-radius:50%; color:#fff; display:flex; align-items:center; justify-content:center; font-size:24px; transition:transform .18s ease; }
        .pl-float-wa{ background:#1FB855; box-shadow:0 14px 32px -8px rgba(31,184,85,.6); }
        .pl-float-call{ background:var(--pl-maroon); box-shadow:0 14px 32px -8px rgba(122,14,36,.5); }
        .pl-float-btn:hover{ transform:scale(1.08); }

        /* ===== Login modal ===== */
        .pl-modal{ display:none; position:fixed; inset:0; z-index:40; align-items:center; justify-content:center; padding:24px; background:rgba(16,11,13,.7); backdrop-filter:blur(3px); }
        .pl-modal.open{ display:flex; }
        .pl-login-card{ background:#fff; border-radius:22px; width:100%; max-width:420px; padding:34px; box-shadow:0 40px 90px rgba(0,0,0,.4); position:relative; overflow:hidden; }
        .pl-login-card::before{ content:''; position:absolute; top:0; left:0; right:0; height:5px; background:var(--pl-gold-gradient); }
        .pl-login-card .close{ position:absolute; top:14px; right:16px; width:36px; height:36px; border:0; background:transparent; cursor:pointer; color:var(--pl-muted); font-size:24px; border-radius:50%; }
        .pl-login-card .close:hover{ background:var(--pl-maroon-soft); }
        .pl-login-card h2{ font-size:26px; margin-bottom:4px; font-weight:800; }
        .pl-login-card .sub{ color:var(--pl-muted); font-size:14.5px; margin-bottom:18px; }
        .pl-error{ background:#FEF2F2; color:#B91C1C; border-radius:10px; padding:11px 14px; margin-bottom:14px; font-size:14px; font-weight:600; }
        .pl-form-group{ margin:14px 0; text-align:left; }
        .pl-form-group label{ font-size:13.5px; font-weight:600; color:var(--pl-muted); }
        .pl-input{ width:100%; padding:13px 14px; border:1.5px solid #E7DFD3; border-radius:10px; margin-top:7px; background:#fff; color:var(--pl-ink); font:500 15px 'Inter',sans-serif; }
        .pl-input:focus{ outline:none; border-color:var(--pl-gold); box-shadow:0 0 0 3px rgba(212,175,55,.2); }
        .pl-pass{ position:relative; }
        .pl-pass .pl-input{ padding-right:46px; }
        .pl-pass button{ position:absolute; right:6px; top:calc(50% + 3px); transform:translateY(-50%); width:36px; height:36px; border:0; background:transparent; cursor:pointer; color:var(--pl-muted); border-radius:8px; }
        .pl-remember{ display:flex; align-items:center; gap:8px; font-size:14px; color:var(--pl-muted); margin-top:4px; }
        .pl-login-safe{ margin-top:16px; display:flex; gap:9px; font-size:13px; color:var(--pl-muted); line-height:1.5; }
        .pl-login-safe i{ color:var(--pl-maroon); margin-top:3px; }

        /* ===== Responsive ===== */
        @media(max-width:1100px){
            .pl-steps{ grid-template-columns:repeat(2,1fr); }
            .pl-calendar{ grid-template-columns:repeat(3,1fr); }
            .pl-nav-links{ display:none; }
        }
        @media(max-width:900px){
            .pl-hero{ padding:40px 0 64px; }
            .pl-hero-grid,.pl-safe-grid,.pl-result-wrap,.pl-footer-grid{ grid-template-columns:1fr; gap:40px; }
            .pl-hero h1{ font-size:40px; }
            html[data-lang="pa"] .pl-hero h1{ font-size:34px; }
            .pl-hero .lead{ font-size:17px; }
            .pl-grid,.pl-trust-row{ grid-template-columns:1fr 1fr; }
            .pl-section{ padding:68px 0; }
            .pl-head h2,.pl-safe h2,.pl-result-wrap h2{ font-size:32px; }
            .pl-bumper h2{ font-size:32px; }
            .pl-safe{ padding:68px 0; }
            .pl-trust{ padding:48px 20px; }
        }
        @media(max-width:700px){
            .container{ padding:0 20px; }
            .pl-nav .row{ min-height:auto; padding:12px 0; }
            .pl-brand{ font-size:16px; }
            .pl-brand-logo,.pl-mark{ width:36px; height:36px; }
            .pl-btn-login-text{ display:none; }
            .pl-grid,.pl-steps,.pl-trust-row{ grid-template-columns:1fr; }
            .pl-calendar{ grid-template-columns:1fr 1fr; }
            .pl-cal-item:last-child{ grid-column:1 / -1; }
            .pl-phase h3{ white-space:normal; font-size:19px; }
            .pl-timebox{ min-width:74px; padding:12px 14px; }
            .pl-timebox strong{ font-size:28px; }
            .pl-note-chip{ position:static; margin-top:14px; max-width:none; }
            .pl-visual{ flex-direction:column; align-items:center; }
            .pl-result-panel{ padding:34px 22px; }
            .pl-float-actions{ right:16px; bottom:16px; }
            .pl-float-btn{ width:52px; height:52px; font-size:21px; }
        }
        @media(prefers-reduced-motion:reduce){
            html{ scroll-behavior:auto; }
            *{ animation:none !important; transition:none !important; }
        }
    </style>
</head>
<body>

{{-- ============================== NAV ============================== --}}
<nav class="pl-nav">
    <div class="container row">
        <a href="#top" class="pl-brand">
            @if(!empty($landing['logo']))
                <img class="pl-brand-logo" src="{{ Storage::url('settings/'.$landing['logo']) }}" alt="Punjab Lottery Seller Logo">
            @else
                <span class="pl-mark"><i class="fas fa-ticket-alt"></i></span>
            @endif
            <span>Punjab Lottery Seller</span>
        </a>

        <div class="pl-nav-links">
            <a href="#tickets">{{ $t('Tickets', 'ਟਿਕਟਾਂ') }}</a>
            <a href="#how">{{ $t('How it works', 'ਕਿਵੇਂ ਕੰਮ ਕਰਦਾ ਹੈ') }}</a>
            <a href="#charges">{{ $t('Charges & safety', 'ਚਾਰਜ ਅਤੇ ਸੁਰੱਖਿਆ') }}</a>
            <a href="#results">{{ $t('Results', 'ਨਤੀਜੇ') }}</a>
        </div>

        <div class="pl-nav-actions">
            <div class="pl-lang-switch" role="group" aria-label="Language">
                <button type="button" data-set="en">EN</button>
                <button type="button" data-set="pa">ਪੰਜਾਬੀ</button>
            </div>
            <a href="#login" class="pl-btn pl-btn-gold pl-btn-sm" data-open-login>
                <i class="fas fa-lock"></i> <span class="pl-btn-login-text">{{ $t('Login', 'ਲੌਗਇਨ') }}</span>
            </a>
        </div>
    </div>
</nav>

{{-- ============================== HERO ============================== --}}
<section class="pl-hero" id="top">
    <div class="container pl-hero-grid">
        <div>
            <div class="pl-kicker">{{ $t($landing['kicker'] ?? 'Punjab State Lottery', $landing['kicker_pa'] ?? null) }}</div>
            <h1>{{ $t($landing['title'] ?? 'Punjab Lottery Portal', $landing['title_pa'] ?? null) }}</h1>
            <p class="lead">{{ $t($landing['subtitle'] ?? '', $landing['subtitle_pa'] ?? null) }}</p>

            <div class="pl-chips">
                <span class="pl-chip">{{ $t('Punjab State Lottery', 'ਪੰਜਾਬ ਸਟੇਟ ਲਾਟਰੀ') }}</span>
                <span class="pl-chip">{{ $t('Punjab Seller Lottery', 'ਪੰਜਾਬ ਸੈਲਰ ਲਾਟਰੀ') }}</span>
                <span class="pl-chip">{{ $t('Weekly', 'ਹਫ਼ਤਾਵਾਰੀ') }}</span>
                <span class="pl-chip">{{ $t('Monthly', 'ਮਹੀਨਾਵਾਰ') }}</span>
                <span class="pl-chip">{{ $t('Bumper tickets', 'ਬੰਪਰ ਟਿਕਟਾਂ') }}</span>
            </div>

            <div class="pl-cta-row">
                <a class="pl-btn pl-btn-wa" href="{{ $waText('Hello, I want to buy a Punjab lottery ticket.') }}" target="_blank" rel="noopener">
                    <i class="fab fa-whatsapp"></i> {{ $t('Buy ticket on WhatsApp', 'ਵਟਸਐਪ ਉੱਤੇ ਟਿਕਟ ਖਰੀਦੋ') }}
                </a>
                <a class="pl-btn pl-btn-outline" href="#login" data-open-login>
                    <i class="fas fa-lock"></i> {{ $t('Login to panel', 'ਪੈਨਲ ਵਿੱਚ ਲੌਗਇਨ ਕਰੋ') }}
                </a>
            </div>

            <div class="pl-perks">
                <span><i class="fas fa-check-circle"></i>{{ $t('Buy on WhatsApp', 'ਵਟਸਐਪ ਉੱਤੇ ਖਰੀਦੋ') }}</span>
                <span><i class="fas fa-check-circle"></i>{{ $t('Track in your panel', 'ਪੈਨਲ ਵਿੱਚ ਵੇਖੋ') }}</span>
                <span><i class="fas fa-check-circle"></i>{{ $t('No hidden charges', 'ਕੋਈ ਲੁਕਵੇਂ ਖਰਚੇ ਨਹੀਂ') }}</span>
            </div>
        </div>

        <div class="pl-visual" aria-hidden="true">
            <div class="pl-note-chip a">
                <span class="ic"><i class="fas fa-trophy"></i></span>
                <span>{{ $t('Winner? The withdrawal option appears in your panel.', 'ਜੇਤੂ ਬਣੇ? ਪੈਸੇ ਕਢਵਾਉਣ ਦਾ ਵਿਕਲਪ ਤੁਹਾਡੇ ਪੈਨਲ ਵਿੱਚ ਆ ਜਾਵੇਗਾ।') }}</span>
            </div>

            <div class="pl-mock">
                <div class="pl-mock-top">
                    <div class="brand">{{ $t('Punjab State Lottery', 'ਪੰਜਾਬ ਸਟੇਟ ਲਾਟਰੀ') }}</div>
                    <h3>{{ $t('Bumper', 'ਬੰਪਰ') }}</h3>
                    <div class="pl-mock-num">
                        <small>{{ $t('Sample ticket number', 'ਨਮੂਨਾ ਟਿਕਟ ਨੰਬਰ') }}</small>
                        48 · 27913
                    </div>
                </div>
                <div class="pl-mock-stub">
                    <span>{{ $t('Physical ticket', 'ਕਾਗਜ਼ੀ ਟਿਕਟ') }}</span>
                    <strong>Rs 500</strong>
                </div>
            </div>

            <div class="pl-note-chip b">
                <span class="ic"><i class="fas fa-id-card"></i></span>
                <span>{{ $t('KYC first, then withdraw.', 'ਪਹਿਲਾਂ ਕੇਵਾਈਸੀ, ਫਿਰ ਪੈਸੇ ਕਢਵਾਓ।') }}</span>
            </div>
        </div>
    </div>
</section>

{{-- ============================== BUMPER COUNTDOWN ============================== --}}
@if($bumperDate)
<section class="pl-bumper" id="bumper">
    <div class="container">
        <div class="eyebrow">
            {{ $t($landing['bumper_title'] ?? 'Bumper Draw', $landing['bumper_title_pa'] ?? null) }}
            &nbsp;·&nbsp;
            {{ $t('Draw date '.$bumperDateEn.', '.$bumperTime, 'ਡਰਾਅ ਦੀ ਤਾਰੀਖ '.$bumperDatePa.', '.$bumperTime) }}
        </div>
        <h2>{{ $t($landing['bumper_prize'] ?? '', $landing['bumper_prize_pa'] ?? null) }}</h2>

        {{-- ISO format => Safari me bhi date sahi parse hoti hai --}}
        <div class="pl-timer" data-time="{{ $bumperDate->toIso8601String() }}">
            <div class="pl-timebox"><strong data-days>00</strong><em class="lbl">{{ $t('Days', 'ਦਿਨ') }}</em></div>
            <div class="pl-timebox"><strong data-hours>00</strong><em class="lbl">{{ $t('Hours', 'ਘੰਟੇ') }}</em></div>
            <div class="pl-timebox"><strong data-minutes>00</strong><em class="lbl">{{ $t('Minutes', 'ਮਿੰਟ') }}</em></div>
            <div class="pl-timebox"><strong data-seconds>00</strong><em class="lbl">{{ $t('Seconds', 'ਸਕਿੰਟ') }}</em></div>
        </div>
        <p class="pl-timer-note" hidden>{{ $t('Draw time has arrived. Check the result in your panel.', 'ਡਰਾਅ ਦਾ ਸਮਾਂ ਹੋ ਗਿਆ ਹੈ। ਨਤੀਜਾ ਆਪਣੇ ਪੈਨਲ ਵਿੱਚ ਵੇਖੋ।') }}</p>

        <p class="fine">{{ $t('*1st Prize is drawn from sold tickets only · Ticket Rs 500 · Prize pool information is managed by admin.', '*ਪਹਿਲਾ ਇਨਾਮ ਸਿਰਫ਼ ਵਿਕੀਆਂ ਹੋਈਆਂ ਟਿਕਟਾਂ ਵਿੱਚੋਂ ਕੱਢਿਆ ਜਾਂਦਾ ਹੈ · ਟਿਕਟ Rs 500 · ਇਨਾਮੀ ਰਕਮ ਦੀ ਜਾਣਕਾਰੀ ਐਡਮਿਨ ਵੱਲੋਂ ਸੰਭਾਲੀ ਜਾਂਦੀ ਹੈ।') }}</p>
    </div>
</section>
@endif

{{-- ============================== TICKETS ============================== --}}
<section class="pl-section" id="tickets">
    <div class="container">
        <div class="pl-head">
            <h2>{{ $t('Choose your lottery ticket', 'ਆਪਣੀ ਲਾਟਰੀ ਟਿਕਟ ਚੁਣੋ') }}</h2>
            <p>{{ $t('Genuine, admin-managed ticket types. Pick the one that suits you, then buy it on WhatsApp.', 'ਅਸਲੀ, ਐਡਮਿਨ ਵੱਲੋਂ ਸੰਭਾਲੀਆਂ ਟਿਕਟਾਂ। ਆਪਣੀ ਪਸੰਦ ਦੀ ਚੁਣੋ ਅਤੇ ਵਟਸਐਪ ਉੱਤੇ ਖਰੀਦੋ।') }}</p>
        </div>

        <div class="pl-grid">
            @forelse(($ticketTypes ?? []) as $type)
                @php
                    $freq  = $type->frequency;
                    $price = (float) $type->ticket_price;
                    $priceText = number_format($price, fmod($price, 1) ? 2 : 0);
                @endphp
                <article class="pl-ticket">
                    <div class="pl-ticket-body">
                        <span class="pl-ribbon">{{ $t(ucfirst($freq), $freqPa[$freq] ?? ucfirst($freq)) }}</span>
                        @if($type->image)
                            <img class="pl-ticket-img" src="{{ Storage::url($type->image) }}" alt="{{ $type->name }}" loading="lazy">
                        @else
                            <div class="icon">{{ $freqIcon[$freq] ?? '🎟' }}</div>
                        @endif
                        <h3>{{ $type->name }}</h3>
                        <p>
                            @if($type->description)
                                {{ $type->description }}
                            @else
                                {{ $t(ucfirst($freq).' lottery ticket with admin-managed prize setup.', ($freqPa[$freq] ?? ucfirst($freq)).' ਲਾਟਰੀ ਟਿਕਟ, ਇਨਾਮ ਐਡਮਿਨ ਵੱਲੋਂ ਤੈਅ ਕੀਤੇ ਜਾਂਦੇ ਹਨ।') }}
                            @endif
                        </p>
                    </div>
                    <div class="pl-ticket-stub">
                        @if($price > 0)
                            <div class="pl-price">
                                <small>{{ $t('Per ticket', 'ਪ੍ਰਤੀ ਟਿਕਟ') }}</small>
                                <strong>₹{{ $priceText }}</strong>
                            </div>
                        @else
                            <span></span>
                        @endif
                        <a class="pl-btn pl-btn-wa pl-btn-sm" href="{{ $waText('Hello, I want to buy: '.$type->name) }}" target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp"></i> {{ $t('Buy', 'ਖਰੀਦੋ') }}
                        </a>
                    </div>
                </article>
            @empty
                <article class="pl-ticket">
                    <div class="pl-ticket-body">
                        <span class="pl-ribbon">{{ $t('Weekly', 'ਹਫ਼ਤਾਵਾਰੀ') }}</span>
                        <div class="icon">🎫</div>
                        <h3>{{ $t('Punjab Weekly Lottery', 'ਪੰਜਾਬ ਹਫ਼ਤਾਵਾਰੀ ਲਾਟਰੀ') }}</h3>
                        <p>{{ $t('Ticket types added by admin will appear here.', 'ਐਡਮਿਨ ਵੱਲੋਂ ਜੋੜੀਆਂ ਟਿਕਟਾਂ ਇੱਥੇ ਦਿਖਾਈ ਦੇਣਗੀਆਂ।') }}</p>
                    </div>
                    <div class="pl-ticket-stub">
                        <span></span>
                        <a class="pl-btn pl-btn-wa pl-btn-sm" href="{{ $waText('Hello, I want to buy a Punjab lottery ticket.') }}" target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp"></i> {{ $t('Ask on WhatsApp', 'ਵਟਸਐਪ ਉੱਤੇ ਪੁੱਛੋ') }}
                        </a>
                    </div>
                </article>
            @endforelse
        </div>
    </div>
</section>

{{-- ============================== HOW IT WORKS ============================== --}}
<section class="pl-section grey" id="how">
    <div class="container">
        <div class="pl-head">
            <h2>{{ $t('From ticket to payout in 6 steps', 'ਟਿਕਟ ਤੋਂ ਭੁਗਤਾਨ ਤੱਕ 6 ਕਦਮਾਂ ਵਿੱਚ') }}</h2>
            <p>{{ $t('Everything is tracked inside your own panel, one step at a time.', 'ਸਭ ਕੁਝ ਤੁਹਾਡੇ ਆਪਣੇ ਪੈਨਲ ਵਿੱਚ ਇੱਕ-ਇੱਕ ਕਦਮ ਕਰਕੇ ਦਿਖਾਈ ਦਿੰਦਾ ਹੈ।') }}</p>
        </div>

        <div class="pl-phase">
            <span class="tag">{{ $t('Part 1', 'ਭਾਗ 1') }}</span>
            <h3>{{ $t('Before the draw', 'ਡਰਾਅ ਤੋਂ ਪਹਿਲਾਂ') }}</h3>
        </div>
        <div class="pl-steps">
            @foreach($stepsBefore as $i => $s)
                <article class="pl-step {{ $s[7] }}">
                    <span class="num">{{ $i + 1 }}</span>
                    <div class="ico"><i class="{{ $s[0] }}"></i></div>
                    <span class="pl-step-chip">{{ $t($s[1], $s[2]) }}</span>
                    <h3>{{ $t($s[3], $s[4]) }}</h3>
                    <p>{{ $t($s[5], $s[6]) }}</p>
                </article>
            @endforeach
        </div>

        <div class="pl-phase">
            <span class="tag">{{ $t('Part 2', 'ਭਾਗ 2') }}</span>
            <h3>{{ $t('After you win', 'ਜਿੱਤਣ ਤੋਂ ਬਾਅਦ') }}</h3>
        </div>
        <div class="pl-steps">
            @foreach($stepsAfter as $i => $s)
                <article class="pl-step {{ $s[7] }}">
                    <span class="num">{{ $i + 4 }}</span>
                    <div class="ico"><i class="{{ $s[0] }}"></i></div>
                    <span class="pl-step-chip">{{ $t($s[1], $s[2]) }}</span>
                    <h3>{{ $t($s[3], $s[4]) }}</h3>
                    <p>{{ $t($s[5], $s[6]) }}</p>
                </article>
            @endforeach
        </div>

        <div class="pl-cta-row" style="justify-content:center;margin-top:48px;">
            <a class="pl-btn pl-btn-wa" href="{{ $waText('Hello, I want to buy a Punjab lottery ticket.') }}" target="_blank" rel="noopener">
                <i class="fab fa-whatsapp"></i> {{ $t('Start on WhatsApp', 'ਵਟਸਐਪ ਤੋਂ ਸ਼ੁਰੂ ਕਰੋ') }}
            </a>
            <a class="pl-btn pl-btn-outline" href="#login" data-open-login>
                <i class="fas fa-lock"></i> {{ $t('Login to panel', 'ਪੈਨਲ ਵਿੱਚ ਲੌਗਇਨ ਕਰੋ') }}
            </a>
        </div>
    </div>
</section>

{{-- ============================== CHARGES & SAFETY ============================== --}}
<section class="pl-safe" id="charges">
    <div class="container pl-safe-grid">
        <div>
            <h2>{{ $t('Charges are shown in your panel, never on a call', 'ਚਾਰਜ ਤੁਹਾਡੇ ਪੈਨਲ ਵਿੱਚ ਦਿਖਦੇ ਹਨ, ਫ਼ੋਨ ਉੱਤੇ ਨਹੀਂ') }}</h2>
            <p class="lead">{{ $t('If any charge applies to your withdrawal, you will see the exact amount inside your own panel. Nobody can ask you for money just by telling you to pay.', 'ਜੇ ਤੁਹਾਡੇ ਪੈਸੇ ਕਢਵਾਉਣ ਉੱਤੇ ਕੋਈ ਚਾਰਜ ਲੱਗਦਾ ਹੈ, ਤਾਂ ਉਸਦੀ ਸਹੀ ਰਕਮ ਤੁਹਾਨੂੰ ਆਪਣੇ ਪੈਨਲ ਵਿੱਚ ਹੀ ਦਿਖਾਈ ਦੇਵੇਗੀ। ਸਿਰਫ਼ ਕਹਿਣ ਉੱਤੇ ਕਿਸੇ ਨੂੰ ਵੀ ਪੈਸੇ ਨਾ ਦਿਓ।') }}</p>

            <ul class="pl-rules">
                <li><span class="mk ok"><i class="fas fa-check"></i></span><span>{{ $t('A charge, if there is one, is visible in your panel with the exact amount.', 'ਜੇ ਕੋਈ ਚਾਰਜ ਹੈ ਤਾਂ ਉਹ ਸਹੀ ਰਕਮ ਸਮੇਤ ਤੁਹਾਡੇ ਪੈਨਲ ਵਿੱਚ ਦਿਖਦਾ ਹੈ।') }}</span></li>
                <li><span class="mk ok"><i class="fas fa-check"></i></span><span>{{ $t('Pay online only when your panel shows the payment.', 'ਸਿਰਫ਼ ਓਦੋਂ ਹੀ ਆਨਲਾਈਨ ਭੁਗਤਾਨ ਕਰੋ ਜਦੋਂ ਤੁਹਾਡੇ ਪੈਨਲ ਵਿੱਚ ਭੁਗਤਾਨ ਦਿਖਾਈ ਦੇਵੇ।') }}</span></li>
                <li><span class="mk no"><i class="fas fa-times"></i></span><span>{{ $t('Do not pay anyone who asks for a charge on a call, WhatsApp or SMS.', 'ਫ਼ੋਨ, ਵਟਸਐਪ ਜਾਂ ਐਸਐਮਐਸ ਉੱਤੇ ਚਾਰਜ ਮੰਗਣ ਵਾਲੇ ਕਿਸੇ ਨੂੰ ਵੀ ਪੈਸੇ ਨਾ ਦਿਓ।') }}</span></li>
                <li><span class="mk no"><i class="fas fa-times"></i></span><span>{{ $t('Never share your password or OTP with anyone.', 'ਆਪਣਾ ਪਾਸਵਰਡ ਜਾਂ ਓਟੀਪੀ ਕਿਸੇ ਨਾਲ ਸਾਂਝਾ ਨਾ ਕਰੋ।') }}</span></li>
            </ul>
        </div>

        <div>
            {{-- Ye sirf illustration hai - asli panel ka nakli preview --}}
            <div class="pl-panel" aria-hidden="true">
                <div class="pl-panel-bar">
                    <span class="dot"></span><span class="dot"></span><span class="dot"></span>
                    <span class="url">{{ $t('Your panel · Withdrawals', 'ਤੁਹਾਡਾ ਪੈਨਲ · ਕਢਵਾਉਣਾ') }}</span>
                </div>
                <div class="pl-panel-body">
                    <div class="pl-panel-title">
                        <h3>{{ $t('Withdrawal request', 'ਪੈਸੇ ਕਢਵਾਉਣ ਦੀ ਬੇਨਤੀ') }}</h3>
                        <span class="pl-badge">{{ $t('Processing', 'ਪ੍ਰਕਿਰਿਆ ਵਿੱਚ') }}</span>
                    </div>
                    <div class="pl-row"><span>{{ $t('Winning amount', 'ਜਿੱਤੀ ਰਕਮ') }}</span><b>₹ ••••••</b></div>
                    <div class="pl-row"><span>{{ $t('Charges (if any)', 'ਚਾਰਜ (ਜੇ ਕੋਈ)') }}</span><b>₹ ••••</b></div>
                    <div class="pl-row net"><span>{{ $t('Net amount you receive', 'ਤੁਹਾਨੂੰ ਮਿਲਣ ਵਾਲੀ ਰਕਮ') }}</span><b>₹ ••••••</b></div>

                    <div class="pl-track">
                        <div class="on">{{ $t('Pending', 'ਲੰਬਿਤ') }}</div>
                        <div class="on">{{ $t('Processing', 'ਪ੍ਰਕਿਰਿਆ') }}</div>
                        <div>{{ $t('Approved', 'ਮਨਜ਼ੂਰ') }}</div>
                        <div>{{ $t('Paid', 'ਭੁਗਤਾਨ ਹੋਇਆ') }}</div>
                    </div>

                    <div class="pl-panel-note">
                        <i class="fas fa-shield-alt"></i>
                        <span>{{ $t('If a charge is due, it shows up in this box. Payment UTR and status updates appear here too.', 'ਜੇ ਕੋਈ ਚਾਰਜ ਦੇਣਾ ਹੋਵੇ ਤਾਂ ਉਹ ਇਸੇ ਡੱਬੇ ਵਿੱਚ ਦਿਖੇਗਾ। ਭੁਗਤਾਨ ਦਾ ਯੂਟੀਆਰ ਅਤੇ ਸਥਿਤੀ ਵੀ ਇੱਥੇ ਹੀ ਅਪਡੇਟ ਹੁੰਦੇ ਹਨ।') }}</span>
                    </div>
                </div>
            </div>
            <p class="pl-panel-cap">{{ $t('Illustration only. Real amounts are visible after you login.', 'ਸਿਰਫ਼ ਨਮੂਨਾ। ਅਸਲੀ ਰਕਮਾਂ ਲੌਗਇਨ ਕਰਨ ਤੋਂ ਬਾਅਦ ਦਿਖਣਗੀਆਂ।') }}</p>
        </div>
    </div>
</section>

{{-- ============================== BUMPER CALENDAR ============================== --}}
<section class="pl-section" id="calendar">
    <div class="container">
        <div class="pl-head">
            <h2>{{ $t('Punjab State Lottery bumper calendar', 'ਪੰਜਾਬ ਸਟੇਟ ਲਾਟਰੀ ਬੰਪਰ ਕੈਲੰਡਰ') }}</h2>
            <p>{{ $t('Punjab State Lotteries run big-prize bumper draws almost every season.', 'ਪੰਜਾਬ ਸਟੇਟ ਲਾਟਰੀਆਂ ਲਗਭਗ ਹਰ ਮੌਸਮ ਵਿੱਚ ਵੱਡੇ ਇਨਾਮਾਂ ਵਾਲੇ ਬੰਪਰ ਡਰਾਅ ਕੱਢਦੀਆਂ ਹਨ।') }}</p>
        </div>
        <div class="pl-calendar">
            @foreach($calendar as $c)
                <div class="pl-cal-item">
                    <div class="ico"><i class="fas {{ $c[0] }}"></i></div>
                    <h3>{{ $t($c[1], $c[2]) }}</h3>
                    <strong>{{ $t($c[3], $c[4]) }}</strong>
                    <p>{{ $t($c[5], $c[6]) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================== RESULT ============================== --}}
<section class="pl-section dark" id="results">
    <div class="container pl-result-wrap">
        <div>
            <h2>{{ $t('How to check your Punjab State Lottery result', 'ਆਪਣਾ ਪੰਜਾਬ ਸਟੇਟ ਲਾਟਰੀ ਨਤੀਜਾ ਕਿਵੇਂ ਵੇਖੋ') }}</h2>
            <p>{{ $t($landing['result_text'] ?? '', $landing['result_text_pa'] ?? null) }}</p>
            <div class="hint"><i class="fas fa-trophy"></i> {{ $t('Winning tickets are also marked in your panel.', 'ਜੇਤੂ ਟਿਕਟਾਂ ਤੁਹਾਡੇ ਪੈਨਲ ਵਿੱਚ ਵੀ ਦਰਜ ਹੁੰਦੀਆਂ ਹਨ।') }}</div>
        </div>
        <div class="pl-result-panel">
            <div class="play-ico"><i class="fas fa-play"></i></div>
            <h3>{{ $t('Watch the live draw', 'ਲਾਈਵ ਡਰਾਅ ਵੇਖੋ') }}</h3>
            <p>{{ $t('Punjab State Lottery draws are streamed live by the official Directorate on draw day.', 'ਪੰਜਾਬ ਸਟੇਟ ਲਾਟਰੀ ਦੇ ਡਰਾਅ, ਡਰਾਅ ਵਾਲੇ ਦਿਨ ਸਰਕਾਰੀ ਡਾਇਰੈਕਟੋਰੇਟ ਵੱਲੋਂ ਲਾਈਵ ਦਿਖਾਏ ਜਾਂਦੇ ਹਨ।') }}</p>
            <a class="pl-btn pl-btn-dark" href="#login" data-open-login>{{ $t('Watch live draw & results', 'ਲਾਈਵ ਡਰਾਅ ਅਤੇ ਨਤੀਜੇ ਵੇਖੋ') }}</a>
        </div>
    </div>
</section>

{{-- ============================== TRUST ============================== --}}
<section class="pl-section" id="trust">
    <div class="container">
        <div class="pl-trust">
            <div class="pl-head">
                <h2>{{ $t('Always buy from trusted sellers', 'ਹਮੇਸ਼ਾ ਭਰੋਸੇਮੰਦ ਵਿਕਰੇਤਾ ਤੋਂ ਹੀ ਖਰੀਦੋ') }}</h2>
            </div>
            <div class="pl-trust-row">
                <div class="pl-trust-item"><span class="ring">✅</span>{{ $t('Genuine tickets', 'ਅਸਲੀ ਟਿਕਟਾਂ') }}</div>
                <div class="pl-trust-item"><span class="ring">📍</span>{{ $t('Visit the shop', 'ਦੁਕਾਨ ਉੱਤੇ ਆਓ') }}</div>
                <div class="pl-trust-item"><span class="ring">🎫</span>{{ $t('Physical ticket', 'ਕਾਗਜ਼ੀ ਟਿਕਟ') }}</div>
                <div class="pl-trust-item"><span class="ring">💬</span>{{ $t('Customer support', 'ਗਾਹਕ ਸਹਾਇਤਾ') }}</div>
            </div>
        </div>
    </div>
</section>

{{-- ============================== FAQ ============================== --}}
<section class="pl-section grey" id="faq">
    <div class="container">
        <div class="pl-head">
            <h2>{{ $t('Questions you may have', 'ਤੁਹਾਡੇ ਮਨ ਵਿੱਚ ਆਉਂਦੇ ਸਵਾਲ') }}</h2>
        </div>
        <div class="pl-faq">
            @foreach($faqs as $f)
                <details>
                    <summary>{{ $t($f[0], $f[1]) }} <i class="fas fa-chevron-down"></i></summary>
                    <p>{{ $t($f[2], $f[3]) }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================== FOOTER ============================== --}}
<footer class="pl-footer">
    <div class="container pl-footer-grid">
        <div>
            @if($bumperDate)
                <h4>{{ $t('Next bumper draw', 'ਅਗਲਾ ਬੰਪਰ ਡਰਾਅ') }}</h4>
                <h3 class="gold">{{ $t($landing['bumper_title'] ?? '', $landing['bumper_title_pa'] ?? null) }}</h3>
                <h4 class="mt">{{ $t('Draw date', 'ਡਰਾਅ ਦੀ ਤਾਰੀਖ') }}</h4>
                <p>{{ $t($bumperDateEn, $bumperDatePa) }}</p>
            @endif
        </div>
        <div>
            <h4>{{ $t('Top Punjab lottery', 'ਪ੍ਰਮੁੱਖ ਪੰਜਾਬ ਲਾਟਰੀ') }}</h4>
            <h3>
                {{ $t('Rakhi Bumper', 'ਰੱਖੜੀ ਬੰਪਰ') }}<br>
                {{ $t('Diwali Bumper', 'ਦੀਵਾਲੀ ਬੰਪਰ') }}<br>
                {{ $t('Lohri Bumper', 'ਲੋਹੜੀ ਬੰਪਰ') }}<br>
                {{ $t('Baisakhi Bumper', 'ਵਿਸਾਖੀ ਬੰਪਰ') }}
            </h3>
        </div>
        <div>
            <h4>{{ $t('Genuine Punjab lottery sellers', 'ਅਸਲੀ ਪੰਜਾਬ ਲਾਟਰੀ ਵਿਕਰੇਤਾ') }}</h4>
            <p>{{ $t($landing['footer_text'] ?? '', $landing['footer_text_pa'] ?? null) }}</p>
            <p>
                {{ $t('WhatsApp', 'ਵਟਸਐਪ') }}: {{ $landing['whatsapp'] ?? '' }}<br>
                {{ $t('Call', 'ਫ਼ੋਨ') }}: {{ $landing['call'] ?? '' }}<br>
                {{ $t('Address', 'ਪਤਾ') }}: {{ $t($landing['address'] ?? '', $landing['address_pa'] ?? null) }}
            </p>
        </div>
    </div>
    <div class="container">
        <div class="pl-copyright">{{ $t('Copyright © '.date('Y').' Ludhiana Lottery. All Rights Reserved.', 'ਕਾਪੀਰਾਈਟ © '.date('Y').' ਲੁਧਿਆਣਾ ਲਾਟਰੀ। ਸਾਰੇ ਹੱਕ ਰਾਖਵੇਂ ਹਨ।') }}</div>
    </div>
</footer>

{{-- ============================== FLOATING ACTIONS ============================== --}}
<div class="pl-float-actions">
    <a class="pl-float-btn pl-float-call" href="{{ $callHref }}" aria-label="Call support"><i class="fas fa-phone-alt"></i></a>
    <a class="pl-float-btn pl-float-wa" href="{{ $waText('Hello, I want to buy a Punjab lottery ticket.') }}" target="_blank" rel="noopener" aria-label="WhatsApp support"><i class="fab fa-whatsapp"></i></a>
</div>

{{-- ============================== LOGIN MODAL ============================== --}}
<div id="login" class="pl-modal" role="dialog" aria-modal="true" aria-labelledby="pl-login-title" data-has-errors="{{ $errors->any() ? '1' : '0' }}">
    <div class="pl-login-card">
        <button type="button" class="close" data-close-login aria-label="Close">&times;</button>
        <h2 id="pl-login-title">{{ $t('Login', 'ਲੌਗਇਨ') }}</h2>
        <p class="sub">{{ $t('Admin and customer panel access.', 'ਐਡਮਿਨ ਅਤੇ ਗਾਹਕ ਪੈਨਲ ਵਿੱਚ ਦਾਖਲਾ।') }}</p>

        @if($errors->any())
            <div class="pl-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="pl-form-group">
                <label for="pl-email">{{ $t('Email', 'ਈਮੇਲ') }}</label>
                <input id="pl-email" class="pl-input" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required>
            </div>
            <div class="pl-form-group">
                <label for="pl-password">{{ $t('Password', 'ਪਾਸਵਰਡ') }}</label>
                <div class="pl-pass">
                    <input id="pl-password" class="pl-input" type="password" name="password" autocomplete="current-password" required>
                    <button type="button" data-toggle-pass aria-label="Show or hide password"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <label class="pl-remember"><input type="checkbox" name="remember"> {{ $t('Remember me', 'ਮੈਨੂੰ ਯਾਦ ਰੱਖੋ') }}</label>
            <button type="submit" class="pl-btn pl-btn-gold" style="width:100%;margin-top:20px;">{{ $t('Sign in', 'ਸਾਈਨ ਇਨ ਕਰੋ') }}</button>
        </form>

        <p class="pl-login-safe"><i class="fas fa-shield-alt"></i><span>{{ $t('Any charge is shown only inside your panel. Never pay because someone asked you to.', 'ਕੋਈ ਵੀ ਚਾਰਜ ਸਿਰਫ਼ ਤੁਹਾਡੇ ਪੈਨਲ ਵਿੱਚ ਦਿਖਦਾ ਹੈ। ਕਿਸੇ ਦੇ ਕਹਿਣ ਉੱਤੇ ਕਦੇ ਭੁਗਤਾਨ ਨਾ ਕਰੋ।') }}</span></p>
    </div>
</div>

<script>
(function () {
    var root = document.documentElement;

    /* ---------- Language toggle ---------- */
    document.querySelectorAll('[data-set]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var l = btn.getAttribute('data-set');
            root.setAttribute('data-lang', l);
            root.setAttribute('lang', l);
            try { localStorage.setItem('pl_lang', l); } catch (e) {}
        });
    });

    /* ---------- Bumper countdown ---------- */
    var timers = document.querySelectorAll('.pl-timer');
    var note = document.querySelector('.pl-timer-note');
    function pad(n) { return String(n).padStart(2, '0'); }
    function tick() {
        timers.forEach(function (t) {
            var diff = Math.max(0, new Date(t.dataset.time).getTime() - Date.now());
            t.querySelector('[data-days]').textContent    = pad(Math.floor(diff / 86400000));
            t.querySelector('[data-hours]').textContent   = pad(Math.floor(diff % 86400000 / 3600000));
            t.querySelector('[data-minutes]').textContent = pad(Math.floor(diff % 3600000 / 60000));
            t.querySelector('[data-seconds]').textContent = pad(Math.floor(diff % 60000 / 1000));
            if (note) { note.hidden = diff > 0; }
        });
    }
    if (timers.length) { setInterval(tick, 1000); tick(); }

    /* ---------- Login modal ---------- */
    var modal = document.getElementById('login');
    function openLogin() {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
        setTimeout(function () { var f = document.getElementById('pl-email'); if (f) f.focus(); }, 60);
    }
    function closeLogin() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
    document.querySelectorAll('[data-open-login]').forEach(function (a) {
        a.addEventListener('click', function (e) { e.preventDefault(); openLogin(); });
    });
    document.querySelectorAll('[data-close-login]').forEach(function (b) { b.addEventListener('click', closeLogin); });
    modal.addEventListener('click', function (e) { if (e.target === modal) closeLogin(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeLogin(); });

    // Wrong password ke baad page reload hota hai - tab modal khud khul jaye taaki error dikhe
    if (modal.getAttribute('data-has-errors') === '1') { openLogin(); }

    /* ---------- Password show/hide ---------- */
    var eye = document.querySelector('[data-toggle-pass]');
    if (eye) {
        eye.addEventListener('click', function () {
            var input = document.getElementById('pl-password');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            eye.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    }
})();
</script>
</body>
</html>
