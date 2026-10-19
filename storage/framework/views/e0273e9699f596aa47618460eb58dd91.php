
<aside class="user-sidebar" id="userSidebar">

    <a href="<?php echo e(route('user.dashboard')); ?>" class="user-brand">
        <span class="brand-mark">L</span>
        <span class="brand-name">Loan<span>Engine</span></span>
    </a>

    <div class="sidebar-section-label">YOUR WORKSPACE</div>

    <nav class="user-nav" aria-label="User navigation">

        <a href="<?php echo e(route('user.dashboard')); ?>"
           class="user-nav-link <?php echo e(request()->routeIs('user.dashboard') ? 'is-active' : ''); ?>"
           title="Dashboard">
            <span class="nav-icon">⌂</span>
            <span>Dashboard</span>
        </a>
        

        <?php if(Route::has('user.education.index')): ?>
            <a href="<?php echo e(route('user.education.index')); ?>"
               class="user-nav-link <?php echo e(request()->routeIs('user.education.*') ? 'is-active' : ''); ?>"
               title="Education">
                <span class="nav-icon">▤</span>
                <span>Education</span>
            </a>
        <?php endif; ?>

        <?php if(Route::has('user.tools.index')): ?>
            <a href="<?php echo e(route('user.tools.index')); ?>"
               class="user-nav-link <?php echo e(request()->routeIs('user.tools.*') ? 'is-active' : ''); ?>"
               title="Loan Tools">
                <span class="nav-icon">⌘</span>
                <span>Loan Tools</span>
            </a>
        <?php elseif(Route::has('tools.index')): ?>
            <a href="<?php echo e(route('tools.index')); ?>"
               class="user-nav-link <?php echo e(request()->routeIs('tools.*') ? 'is-active' : ''); ?>"
               title="Loan Tools">
                <span class="nav-icon">⌘</span>
                <span>Loan Tools</span>
            </a>
        <?php endif; ?>

        <?php if(Route::has('user.media.index')): ?>
            <a href="<?php echo e(route('user.media.index')); ?>"
               class="user-nav-link <?php echo e(request()->routeIs('user.media.*') ? 'is-active' : ''); ?>"
               title="Learning Resources">
                <span class="nav-icon">▷</span>
                <span>Learning Resources</span>
            </a>
        <?php endif; ?>

        <?php if(Route::has('user.news.index')): ?>
            <a href="<?php echo e(route('user.news.index')); ?>"
               class="user-nav-link <?php echo e(request()->routeIs('user.news.*') ? 'is-active' : ''); ?>"
               title="News & Updates">
                <span class="nav-icon">▧</span>
                <span>News & Updates</span>
            </a>
        <?php endif; ?>

    </nav>

    <div class="sidebar-bottom">

        <div class="sidebar-user">
            <div class="sidebar-avatar">
                <?php echo e(strtoupper(substr(auth()->user()->name ?? 'U', 0, 1))); ?>

            </div>

            <div class="sidebar-user-copy">
                <strong><?php echo e(auth()->user()->name ?? 'User'); ?></strong>
                <small><?php echo e(auth()->user()->email ?? ''); ?></small>
            </div>
        </div>

        <form method="POST" action="<?php echo e(route('auth.logout')); ?>">
            <?php echo csrf_field(); ?>

            <button type="submit" class="user-logout" title="Logout">
                <span class="nav-icon">↪</span>
                <span>Logout</span>
            </button>
        </form>

    </div>

</aside>
<?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/user/partials/sidebar.blade.php ENDPATH**/ ?>