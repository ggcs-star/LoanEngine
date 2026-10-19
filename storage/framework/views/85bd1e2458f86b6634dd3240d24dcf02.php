
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', 'User Dashboard'); ?> - LoanEngine</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo e(asset('css/user-theme.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/user-layout.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/user-dashboard.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/user-education.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/user-tools.css')); ?>">

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>

<div class="user-app" id="userApp">

    <?php echo $__env->make('user.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="user-main">

        <header class="user-topbar">

            <button
                type="button"
                class="sidebar-toggle"
                id="sidebarToggle"
                aria-label="Toggle sidebar"
                aria-controls="userSidebar"
                aria-expanded="true"
            >
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="topbar-heading">
                <span class="topbar-eyebrow">LOANENGINE / USER WORKSPACE</span>
                <h1><?php echo $__env->yieldContent('page_heading', 'Dashboard'); ?></h1>
            </div>

            <div class="topbar-profile">
                <div class="profile-avatar">
                    <?php echo e(strtoupper(substr(auth()->user()->name ?? 'U', 0, 1))); ?>

                </div>

                <div class="profile-copy">
                    <strong><?php echo e(auth()->user()->name ?? 'User'); ?></strong>
                    <small><?php echo e(auth()->user()->email ?? ''); ?></small>
                </div>
            </div>

        </header>

        <main class="user-content">

            <?php if(session('success')): ?>
                <div class="user-alert user-alert-success">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="user-alert user-alert-error">
                    <strong>Please correct the following errors:</strong>
                    <ul>
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>

        </main>

        <footer class="user-footer">
            <span>© <?php echo e(date('Y')); ?> LoanEngine</span>
            <span>Make informed financial decisions.</span>
        </footer>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('userApp');
    const sidebar = document.getElementById('userSidebar');
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    if (!app || !sidebar || !toggle || !overlay) {
        return;
    }

    const mobileQuery = window.matchMedia('(max-width: 700px)');

    function isMobile() {
        return mobileQuery.matches;
    }

    function openMobileSidebar() {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-visible');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('sidebar-open');
    }

    function closeMobileSidebar() {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('sidebar-open');
    }

    function toggleDesktopSidebar() {
        const collapsed = app.classList.toggle('sidebar-collapsed');
        toggle.setAttribute('aria-expanded', String(!collapsed));
    }

    toggle.addEventListener('click', function () {
        if (isMobile()) {
            if (sidebar.classList.contains('is-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        } else {
            toggleDesktopSidebar();
        }
    });

    overlay.addEventListener('click', closeMobileSidebar);

    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (isMobile()) {
                closeMobileSidebar();
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMobileSidebar();
        }
    });

    function handleViewportChange() {
        closeMobileSidebar();

        if (isMobile()) {
            app.classList.remove('sidebar-collapsed');
            toggle.setAttribute('aria-expanded', 'false');
        } else {
            toggle.setAttribute(
                'aria-expanded',
                String(!app.classList.contains('sidebar-collapsed'))
            );
        }
    }

    if (mobileQuery.addEventListener) {
        mobileQuery.addEventListener('change', handleViewportChange);
    } else {
        mobileQuery.addListener(handleViewportChange);
    }

    handleViewportChange();
});
</script>

<?php echo $__env->yieldPushContent('scripts'); ?>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/user/layouts/app.blade.php ENDPATH**/ ?>