<?php
require_once __DIR__ . '/config/app.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZPPSU HRMU - Employee Clearance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .landing-page {
            min-height: 100vh;
            background: var(--maroon-dark) url('assets/images/bg1.jpg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .landing-page::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(30, 0, 0, 0.7);
        }
        .landing-decoration {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }
        .landing-decoration:nth-child(1) {
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(212,175,55,0.06), transparent 70%);
            top: -300px; right: -200px;
        }
        .landing-decoration:nth-child(2) {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(212,175,55,0.05), transparent 70%);
            bottom: -200px; left: -150px;
        }
        .landing-decoration:nth-child(3) {
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.04), transparent 70%);
            top: 20%; left: 50%;
            transform: translateX(-50%);
        }
        .landing-content {
            position: relative;
            z-index: 1;
            width: 100%;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .landing-hero {
            text-align: center;
            padding: 3rem 2rem 2rem;
        }
        .landing-logo {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 40px rgba(212,175,55,0.3);
            border: 3px solid rgba(255,255,255,0.2);
        }
        .landing-logo img { width: 76px; height: 76px; }
        .landing-hero h1 {
            color: var(--white);
            font-size: 2.8rem;
            font-weight: 900;
            letter-spacing: -1px;
            margin-bottom: 0.25rem;
        }
        .landing-hero .subtitle {
            color: var(--gold);
            font-size: 1.1rem;
            font-weight: 400;
            margin-bottom: 0.25rem;
        }
        .landing-hero .badge-title {
            display: inline-block;
            background: rgba(212,175,55,0.2);
            border: 1px solid rgba(212,175,55,0.3);
            color: var(--gold-light);
            padding: 0.35rem 1.2rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }
        .landing-hero .desc {
            color: var(--gold-light);
            font-size: 1.05rem;
            max-width: 600px;
            margin: 0 auto 2rem;
            line-height: 1.7;
        }
        .landing-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .landing-btn-primary {
            padding: 0.9rem 2.5rem;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            border: none;
            border-radius: 12px;
            color: var(--maroon-dark);
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.3px;
            text-decoration: none;
            transition: all var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .landing-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 40px rgba(212,175,55,0.3);
            color: var(--maroon-dark);
        }
        .landing-btn-outline {
            padding: 0.85rem 2rem;
            background: transparent;
            border: 2px solid rgba(255,255,255,0.25);
            border-radius: 12px;
            color: var(--white);
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        .landing-btn-outline:hover {
            border-color: var(--gold);
            color: var(--gold);
            transform: translateY(-3px);
        }

        .landing-features {
            padding: 2.5rem 2rem 1.5rem;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }
        .landing-features h3 {
            color: rgba(255,255,255,0.5);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }
        .feature-card {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 1.5rem 1rem;
            text-align: center;
            transition: all var(--transition);
        }
        .feature-card:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(212,175,55,0.3);
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.15);
        }
        .feature-card i {
            font-size: 2rem;
            color: var(--gold);
            margin-bottom: 0.75rem;
            display: block;
        }
        .feature-card strong {
            display: block;
            color: var(--white);
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
        }
        .feature-card span {
            color: rgba(255,255,255,0.5);
            font-size: 0.78rem;
            line-height: 1.5;
            display: block;
        }

        .landing-steps {
            padding: 1.5rem 2rem 2.5rem;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
        }
        .landing-steps h3 {
            color: rgba(255,255,255,0.5);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .steps-row {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .step-chip {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 50px;
            padding: 0.45rem 1rem 0.45rem 0.45rem;
            transition: all var(--transition);
        }
        .step-chip:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(212,175,55,0.3);
        }
        .step-chip .num {
            width: 28px; height: 28px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--maroon-dark);
            font-weight: 800;
            font-size: 0.75rem;
            flex-shrink: 0;
        }
        .step-chip span {
            color: rgba(255,255,255,0.75);
            font-size: 0.82rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .landing-marquee {
            padding: 0 2rem 3rem;
            max-width: 700px;
            margin: 0 auto;
            width: 100%;
        }
        .marquee-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
        }
        .marquee-badge .item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: rgba(255,255,255,0.5);
            font-size: 0.78rem;
            font-weight: 500;
        }
        .marquee-badge .item i { color: var(--success); font-size: 0.7rem; }

        .landing-footer {
            text-align: center;
            padding: 1.5rem 2rem;
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .landing-footer p {
            color: rgba(255,255,255,0.25);
            font-size: 0.75rem;
            margin: 0;
        }

        @media (max-width: 768px) {
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .landing-hero h1 { font-size: 2rem; }
            .landing-hero .desc { font-size: 0.95rem; }
            .landing-hero { padding: 2rem 1.5rem 1.5rem; }
            .landing-features { padding: 2rem 1.5rem 1rem; }
            .steps-row { gap: 0.5rem; }
            .step-chip span { font-size: 0.75rem; }
            .marquee-badge { border-radius: 16px; padding: 0.75rem 1rem; }
        }
        @media (max-width: 576px) {
            .features-grid { grid-template-columns: 1fr 1fr; gap: 0.75rem; }
            .feature-card { padding: 1.25rem 0.75rem; }
            .feature-card i { font-size: 1.5rem; }
            .landing-hero h1 { font-size: 1.6rem; }
            .landing-btn-primary, .landing-btn-outline { padding: 0.75rem 1.5rem; font-size: 0.9rem; }
            .step-chip { padding: 0.35rem 0.75rem 0.35rem 0.35rem; }
            .step-chip span { font-size: 0.7rem; }
        }
    </style>
</head>
<body class="landing-page">

    <div class="landing-decoration"></div>
    <div class="landing-decoration"></div>
    <div class="landing-decoration"></div>

    <div class="landing-content">
        <div class="landing-hero">
            <div class="landing-logo">
                <img src="<?= assetUrl('assets/images/zppsu_logo.png') ?>" alt="ZPPSU">
            </div>
            <div class="badge-title">Zamboanga Peninsula Polytechnic State University</div>
            <h1>HRMU Clearance</h1>
            <p class="subtitle">Employee Clearance Management System</p>
            <p class="desc">
                A comprehensive digital platform for faculty and staff clearance processing.
                Submit requests, track real-time progress, and receive digitally signed certificates — 
                all within a secure, paperless workflow designed for ZPPSU.
            </p>

            <div class="landing-actions">
                <a href="login-page.php" class="landing-btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </a>
                <button type="button" class="landing-btn-outline" data-bs-toggle="modal" data-bs-target="#helpModal">
                    <i class="bi bi-question-circle"></i> Need Help?
                </button>
            </div>
        </div>

        <div class="landing-features">
            <h3>Key Features</h3>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="bi bi-pen"></i>
                    <strong>Digital Signatures</strong>
                    <span>Canvas-based e-signature pad for legal sign-off</span>
                </div>
                <div class="feature-card">
                    <i class="bi bi-diagram-3"></i>
                    <strong>Dynamic Workflow</strong>
                    <span>Auto-routed to deans, offices, and executives</span>
                </div>
                <div class="feature-card">
                    <i class="bi bi-file-earmark-pdf"></i>
                    <strong>Auto PDF Certificates</strong>
                    <span>Generated with QR and tamper-proof protection</span>
                </div>
                <div class="feature-card">
                    <i class="bi bi-qr-code"></i>
                    <strong>QR Verification</strong>
                    <span>Public clearance verification via QR scan</span>
                </div>
            </div>
        </div>

        <div class="landing-steps">
            <h3>How It Works</h3>
            <div class="steps-row">
                <div class="step-chip">
                    <div class="num">1</div>
                    <span>Submit Request</span>
                </div>
                <div class="step-chip">
                    <div class="num">2</div>
                    <span>Department Review</span>
                </div>
                <div class="step-chip">
                    <div class="num">3</div>
                    <span>College Dean Signs</span>
                </div>
                <div class="step-chip">
                    <div class="num">4</div>
                    <span>Office Clearances</span>
                </div>
                <div class="step-chip">
                    <div class="num">5</div>
                    <span>VP &amp; President</span>
                </div>
            </div>
        </div>

        <div class="landing-marquee">
            <div class="marquee-badge">
                <div class="item"><i class="bi bi-check-circle-fill"></i> Paperless Process</div>
                <div class="item"><i class="bi bi-check-circle-fill"></i> Real-time Tracking</div>
                <div class="item"><i class="bi bi-check-circle-fill"></i> Secure &amp; Audited</div>
                <div class="item"><i class="bi bi-check-circle-fill"></i> ZPPSU Compliant</div>
            </div>
        </div>
    </div>

    <div class="landing-footer">
        <p>&copy; <?= date('Y') ?> Zamboanga Peninsula Polytechnic State University. All rights reserved.</p>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4" style="background: rgba(30,0,0,0.95); backdrop-filter: blur(20px); border: 1px solid rgba(212,175,55,0.2); border-radius: 20px;">
                <div class="modal-header border-0 p-0 pb-3">
                    <h5 class="modal-title text-gold fw-bold"><i class="bi bi-question-circle me-2"></i>Need Help?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <p class="text-white-50 mb-3">If you need assistance with the clearance system, here's how to get help:</p>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center gap-3 p-3" style="background: rgba(255,255,255,0.05); border-radius: 12px;">
                            <i class="bi bi-person-badge text-gold fs-4"></i>
                            <div>
                                <strong class="text-white" style="font-size:0.9rem;">Contact HRMO</strong>
                                <p class="text-white-50 mb-0" style="font-size:0.8rem;">For account creation and employee record issues</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 p-3" style="background: rgba(255,255,255,0.05); border-radius: 12px;">
                            <i class="bi bi-headset text-gold fs-4"></i>
                            <div>
                                <strong class="text-white" style="font-size:0.9rem;">IT Support</strong>
                                <p class="text-white-50 mb-0" style="font-size:0.8rem;">For technical issues and system access problems</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 p-3" style="background: rgba(255,255,255,0.05); border-radius: 12px;">
                            <i class="bi bi-file-text text-gold fs-4"></i>
                            <div>
                                <strong class="text-white" style="font-size:0.9rem;">User Guide</strong>
                                <p class="text-white-50 mb-0" style="font-size:0.8rem;">Refer to the employee manual for step-by-step instructions</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
        $(document).ready(function() {
            const fadeEls = [
                '.landing-logo', '.badge-title', 'h1', '.subtitle', '.desc',
                '.landing-actions', '.landing-features', '.landing-steps',
                '.landing-marquee', '.landing-footer'
            ];
            fadeEls.forEach((sel, i) => {
                $(sel).css('opacity', 0);
                setTimeout(() => {
                    $(sel).css({ opacity: 1, transition: 'opacity 0.6s ease, transform 0.6s ease' });
                }, 100 + i * 120);
            });
        });
    </script>
</body>
</html>