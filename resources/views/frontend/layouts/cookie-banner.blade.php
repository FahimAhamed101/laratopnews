{{-- Google AdSense & GDPR / CCPA Compliant Cookie Consent Banner --}}
<div id="gdpr-cookie-consent-banner" class="gdpr-cookie-banner d-none" role="dialog" aria-live="polite" aria-label="Cookie Consent Banner">
    <div class="container">
        <div class="gdpr-cookie-card">
            <div class="gdpr-cookie-content">
                <div class="gdpr-cookie-header">
                    <span class="gdpr-cookie-icon">
                        <i class="fa fa-shield-alt"></i>
                    </span>
                    <h5 class="gdpr-cookie-title">{{ __('frontend.cookie_consent_title') }}</h5>
                </div>
                <p class="gdpr-cookie-text">
                    {{ __('frontend.cookie_consent_text') }}
                    <a href="{{ route('privacy-policy') }}" class="gdpr-cookie-link" target="_blank" rel="noopener">
                        {{ __('frontend.cookie_learn_more') }} &rarr;
                    </a>
                </p>
            </div>
            <div class="gdpr-cookie-actions">
                <button type="button" id="gdpr-cookie-accept" class="btn btn-gdpr-accept">
                    <i class="fa fa-check mr-1"></i> {{ __('frontend.cookie_accept_all') }}
                </button>
                <button type="button" id="gdpr-cookie-necessary" class="btn btn-gdpr-necessary">
                    {{ __('frontend.cookie_necessary_only') }}
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.gdpr-cookie-banner {
    position: fixed;
    bottom: 20px;
    left: 0;
    right: 0;
    z-index: 999999;
    padding: 0 15px;
    animation: gdprSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.gdpr-cookie-card {
    background: #0f172a;
    color: #f8fafc;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.05);
    padding: 22px 28px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    backdrop-filter: blur(12px);
}

.gdpr-cookie-content {
    flex: 1 1 500px;
}

.gdpr-cookie-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.gdpr-cookie-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: rgba(16, 102, 255, 0.15);
    color: #3b82f6;
    border-radius: 8px;
    font-size: 0.95rem;
}

.gdpr-cookie-title {
    margin: 0;
    color: #ffffff;
    font-size: 1.12rem;
    font-weight: 700;
    letter-spacing: -0.01em;
}

.gdpr-cookie-text {
    margin: 0;
    color: #cbd5e1;
    font-size: 0.92rem;
    line-height: 1.6;
}

.gdpr-cookie-link {
    color: #60a5fa;
    text-decoration: underline;
    font-weight: 600;
    margin-left: 6px;
    transition: color 0.2s ease;
}

.gdpr-cookie-link:hover {
    color: #93c5fd;
    text-decoration: none;
}

.gdpr-cookie-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-gdpr-accept {
    background: #1066ff;
    color: #ffffff !important;
    border: none;
    padding: 10px 22px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.92rem;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 4px 12px rgba(16, 102, 255, 0.3);
}

.btn-gdpr-accept:hover {
    background: #0d54d4;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(16, 102, 255, 0.45);
}

.btn-gdpr-necessary {
    background: rgba(255, 255, 255, 0.08);
    color: #e2e8f0 !important;
    border: 1px solid rgba(255, 255, 255, 0.15);
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.92rem;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-gdpr-necessary:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff !important;
}

@keyframes gdprSlideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .gdpr-cookie-card {
        padding: 18px 20px;
    }
    .gdpr-cookie-actions {
        width: 100%;
        display: flex;
        flex-direction: column;
    }
    .btn-gdpr-accept,
    .btn-gdpr-necessary {
        width: 100%;
        text-align: center;
        justify-content: center;
    }
}
</style>

<script>
(function() {
    function getStoredConsent() {
        try {
            return localStorage.getItem('cookie_consent') ||
                (document.cookie.match(/(?:^|;\s*)cookie_consent=([^;]+)/) || [])[1];
        } catch(e) {
            return null;
        }
    }

    function saveConsent(status) {
        try {
            localStorage.setItem('cookie_consent', status);
            // 1-year expiration for consent cookie
            document.cookie = "cookie_consent=" + encodeURIComponent(status) + "; path=/; max-age=31536000; SameSite=Lax";
        } catch(e) {}

        // Google Consent Mode v2 Signal Update
        if (typeof window.gtag === 'function') {
            if (status === 'accepted') {
                window.gtag('consent', 'update', {
                    'ad_storage': 'granted',
                    'ad_user_data': 'granted',
                    'ad_personalization': 'granted',
                    'analytics_storage': 'granted'
                });
            } else {
                window.gtag('consent', 'update', {
                    'ad_storage': 'denied',
                    'ad_user_data': 'denied',
                    'ad_personalization': 'denied',
                    'analytics_storage': 'denied'
                });
            }
        }
    }

    function showBanner() {
        var banner = document.getElementById('gdpr-cookie-consent-banner');
        if (banner) {
            banner.classList.remove('d-none');
        }
    }

    function hideBanner() {
        var banner = document.getElementById('gdpr-cookie-consent-banner');
        if (banner) {
            banner.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            banner.style.opacity = '0';
            banner.style.transform = 'translateY(20px)';
            setTimeout(function() {
                banner.classList.add('d-none');
            }, 300);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var consent = getStoredConsent();

        // If user hasn't made a choice yet, display the banner
        if (!consent) {
            setTimeout(showBanner, 500);
        }

        var acceptBtn = document.getElementById('gdpr-cookie-accept');
        if (acceptBtn) {
            acceptBtn.addEventListener('click', function() {
                saveConsent('accepted');
                hideBanner();
            });
        }

        var necessaryBtn = document.getElementById('gdpr-cookie-necessary');
        if (necessaryBtn) {
            necessaryBtn.addEventListener('click', function() {
                saveConsent('necessary');
                hideBanner();
            });
        }

        // Allow users to re-open cookie preferences anytime from footer
        var openSettingsBtn = document.getElementById('open-cookie-settings');
        if (openSettingsBtn) {
            openSettingsBtn.addEventListener('click', function(e) {
                e.preventDefault();
                showBanner();
            });
        }
    });
})();
</script>
