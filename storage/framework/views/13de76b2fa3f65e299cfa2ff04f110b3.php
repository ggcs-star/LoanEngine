
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', 'Admin Dashboard'); ?> | LoanEngine</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/admin-theme.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/admin-dashboard.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/education.css')); ?>">
     <link rel="stylesheet" href="<?php echo e(asset('css/education-modules.css')); ?>">
     <link rel="stylesheet" href="<?php echo e(asset('css/education-categories.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/education-lessons.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/tools-admin.css')); ?>">

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <div class="admin-shell">
        <?php echo $__env->make('layouts.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="admin-main">
            <?php echo $__env->make('layouts.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <main class="page-content">
                <?php echo $__env->yieldContent('content'); ?>
            </main>

            <footer class="page-footer">
                <span>© <?php echo e(date('Y')); ?> LoanEngine</span>
                <span>Simple. Secure. Reliable.</span>
            </footer>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuButton = document.querySelector('[data-sidebar-toggle]');
            const sidebar = document.querySelector('.admin-sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            function closeSidebar() {
                sidebar?.classList.remove('is-open');
                overlay?.classList.remove('is-visible');
                document.body.classList.remove('sidebar-open');
            }

            menuButton?.addEventListener('click', function () {
                sidebar?.classList.toggle('is-open');
                overlay?.classList.toggle('is-visible');
                document.body.classList.toggle('sidebar-open');
            });

            overlay?.addEventListener('click', closeSidebar);

            document.querySelectorAll('[data-local-search]').forEach(function (input) {
                input.addEventListener('input', function () {
                    const term = input.value.trim().toLowerCase();

                    document.querySelectorAll('[data-searchable]').forEach(function (item) {
                        item.hidden = term !== '' &&
                            !item.textContent.toLowerCase().includes(term);
                    });
                });
            });

            document.querySelectorAll('[data-static-action]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const message = button.dataset.staticAction;
                    const status = document.querySelector('[data-action-status]');

                    if (status) {
                        status.textContent = message;
                        status.hidden = false;
                    }
                });
            });
        });
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/layouts/admin.blade.php ENDPATH**/ ?>