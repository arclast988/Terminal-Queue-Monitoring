<?php
/**
 * Shared Logout Confirmation Modal for Admin & Dispatcher (Staff)
 * Rendered at the root level of the header/navbar partials.
 */
$userName = esc(session()->get('full_name') ?? session()->get('username') ?? 'User');
$userRole = session()->get('role');
$roleLabel = match($userRole) {
    'super_admin' => 'Super Admin',
    'admin'       => 'Admin',
    'staff'       => 'Dispatcher',
    default       => 'User'
};
?>

<!-- Modern Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px; margin: 1.75rem auto;">
        <div class="modal-content logout-modal-content">
            <!-- Accent stripe -->
            <div class="logout-stripe"></div>
            
            <div class="modal-body text-center p-4">
                <!-- Icon badge -->
                <div class="logout-icon-wrapper">
                    <i class="fas fa-arrow-right-from-bracket"></i>
                </div>
                
                <h4 class="logout-modal-title" id="logoutModalLabel">Confirm Logout</h4>
                <p class="logout-modal-desc">
                    Are you sure you want to end your current session? Any unsaved changes will be lost.
                </p>

                <!-- Current User Badge -->
                <div class="logout-user-chip">
                    <i class="fas fa-circle-user"></i>
                    <span class="text-truncate">Signed in as <strong><?= $userName ?></strong></span>
                    <span class="logout-role-tag"><?= $roleLabel ?></span>
                </div>
            </div>

            <div class="modal-footer logout-modal-footer">
                <button type="button" class="btn logout-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Stay Signed In
                </button>
                <a href="<?= base_url('logout') ?>" class="btn logout-btn-confirm" id="btnConfirmLogout">
                    <i class="fas fa-sign-out-alt me-1"></i> Yes, Log Out
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Scoped Logout Modal Styles */
.logout-modal-content {
    border: 0 !important;
    border-radius: 18px !important;
    box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.25), 0 10px 15px -5px rgba(15, 23, 42, 0.1) !important;
    background: #ffffff !important;
    overflow: hidden !important;
    position: relative !important;
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

.logout-stripe {
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, #dc2626 0%, #ef4444 50%, #f97316 100%);
}

.logout-icon-wrapper {
    width: 68px;
    height: 68px;
    margin: 4px auto 18px auto;
    border-radius: 50%;
    background: #fef2f2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    box-shadow: 0 0 0 8px #fff1f2;
    transition: transform 0.3s ease;
}

.logout-modal-content:hover .logout-icon-wrapper {
    transform: scale(1.04);
}

.logout-modal-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
    letter-spacing: -0.01em;
}

.logout-modal-desc {
    color: #64748b;
    font-size: 0.925rem;
    line-height: 1.5;
    margin-bottom: 16px;
    padding: 0 10px;
}

.logout-user-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 7px 14px;
    border-radius: 9999px;
    font-size: 0.825rem;
    color: #334155;
    max-width: 100%;
    box-sizing: border-box;
}

.logout-user-chip i {
    color: #64748b;
    font-size: 1rem;
    flex-shrink: 0;
}

.logout-role-tag {
    background: #fee2e2;
    color: #b91c1c;
    font-size: 0.725rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    flex-shrink: 0;
}

.logout-modal-footer {
    border: 0 !important;
    padding: 0 24px 24px 24px !important;
    display: flex !important;
    gap: 12px !important;
    justify-content: stretch !important;
    background: transparent !important;
}

.logout-btn-cancel {
    flex: 1 !important;
    background: #f1f5f9 !important;
    color: #475569 !important;
    border: 1px solid #e2e8f0 !important;
    padding: 10px 18px !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.logout-btn-cancel:hover,
.logout-btn-cancel:focus {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #cbd5e1 !important;
}

.logout-btn-confirm {
    flex: 1 !important;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
    color: #ffffff !important;
    border: 0 !important;
    padding: 10px 18px !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.28) !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-decoration: none !important;
}

.logout-btn-confirm:hover,
.logout-btn-confirm:focus {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.38) !important;
    transform: translateY(-1px);
}

/* Ensure modal paints above everything including fixed header */
body.modal-open #logoutModal,
#logoutModal {
    z-index: 100050 !important;
}

@media (max-width: 480px) {
    .logout-modal-footer {
        flex-direction: column-reverse !important;
        gap: 8px !important;
        padding: 0 16px 20px 16px !important;
    }
    .logout-modal-footer .btn {
        width: 100% !important;
    }
    .logout-user-chip {
        font-size: 0.775rem;
    }
}

/* --- Dispatcher (staff) green-themed overrides --- */
body.staff-theme .logout-stripe {
    background: linear-gradient(90deg, #15803d 0%, #16a34a 50%, #10b981 100%);
}

body.staff-theme .logout-icon-wrapper {
    background: #dcfce7;
    color: #15803d;
    box-shadow: 0 0 0 8px #f0fdf4;
}

body.staff-theme .logout-role-tag {
    background: #dcfce7;
    color: #166534;
}

body.staff-theme .logout-btn-confirm {
    background: linear-gradient(135deg, #15803d 0%, #166534 100%) !important;
    box-shadow: 0 4px 12px rgba(21, 128, 61, 0.28) !important;
}

body.staff-theme .logout-btn-confirm:hover,
body.staff-theme .logout-btn-confirm:focus {
    background: linear-gradient(135deg, #166534 0%, #14532d 100%) !important;
    box-shadow: 0 6px 16px rgba(21, 128, 61, 0.38) !important;
}
</style>

<script>
/**
 * Global Logout Confirmation Handler
 * Triggers modern Bootstrap modal if available, or native browser confirmation fallback.
 */
function confirmLogout(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    // Track whether logout was initiated from the user profile dropdown
    var profileDropdownEl = document.getElementById('userProfileDropdown');
    if ((event && event.target && event.target.closest('#userProfileDropdown')) || (profileDropdownEl && profileDropdownEl.classList.contains('open'))) {
        window._logoutFromProfileDropdown = true;
    } else {
        window._logoutFromProfileDropdown = false;
    }
    window._logoutConfirmed = false;

    // If mobile navigation drawer is currently open, close it cleanly first
    var adminMobileMenu = document.querySelector('header .nav-menu.mobile-open');
    if (adminMobileMenu && typeof toggleAdminMobileMenu === 'function') {
        toggleAdminMobileMenu();
    } else if (adminMobileMenu) {
        adminMobileMenu.classList.remove('mobile-open');
        var overlay = document.getElementById('mobileNavOverlay');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    var guestMobileMenu = document.querySelector('#navMenu.open');
    if (guestMobileMenu && typeof toggleMenu === 'function') {
        toggleMenu();
    }

    var modalEl = document.getElementById('logoutModal');
    if (modalEl && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        try {
            var modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show();
            return false;
        } catch (err) {
            console.warn('Bootstrap modal trigger error:', err);
        }
    }

    // Native fallback if Bootstrap is not available
    if (window.confirm("Are you sure you want to log out of Palompon Transit Terminal Monitor?")) {
        var logoutBtn = document.getElementById('btnConfirmLogout');
        window.location.href = logoutBtn ? logoutBtn.href : "<?= base_url('logout') ?>";
    }
    return false;
}

document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('logoutModal');
    var confirmBtn = document.getElementById('btnConfirmLogout');
    var cancelBtn = modalEl ? modalEl.querySelector('.logout-btn-cancel') : null;

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            window._logoutConfirmed = true;
        });
    }

    function keepProfileDropdownOpen() {
        if (!window._logoutConfirmed && window._logoutFromProfileDropdown) {
            var profileDropdown = document.getElementById('userProfileDropdown');
            var profileBtn = document.getElementById('userProfileBtn');
            if (profileDropdown) {
                profileDropdown.classList.add('open');
                if (profileBtn) profileBtn.setAttribute('aria-expanded', 'true');
            }
        }
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            keepProfileDropdownOpen();
        });
    }

    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            keepProfileDropdownOpen();
            window._logoutFromProfileDropdown = false;
        });
    }
});
</script>
