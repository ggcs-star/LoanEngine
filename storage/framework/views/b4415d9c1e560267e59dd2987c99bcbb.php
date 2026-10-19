<?php $__env->startSection('title', 'Login'); ?>

<?php $__env->startSection('content'); ?>
    <h1>Welcome to Loan Engine</h1>

    <p class="auth-subtitle">
        Sign in to access your dashboard and continue managing your lending operations.
    </p>

    <div class="auth-tabs">
        <a class="auth-tab is-active" href="<?php echo e(route('auth.login')); ?>">
            Sign In
        </a>
        <a class="auth-tab" href="<?php echo e(route('auth.register')); ?>">
            Register
        </a>
    </div>

    <?php if(session('status')): ?>
        <div class="notice notice-success" role="status">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="notice notice-error" role="alert">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <form method="POST"
          action="<?php echo e(route('auth.login.submit')); ?>"
          class="auth-form">
        <?php echo csrf_field(); ?>

        <div class="field">
            <label for="email">Email Address</label>
            <input
                id="email"
                name="email"
                type="email"
                value="<?php echo e(old('email')); ?>"
                placeholder="Enter your email address"
                autocomplete="email"
                required
                autofocus
            >
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="field-error"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="field">
            <label for="password">Password</label>

            <div class="password-wrapper">
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

                <button
                    type="button"
                    class="password-toggle"
                    data-password-toggle="password"
                    aria-label="Show password"
                    aria-pressed="false"
                >
                    <svg viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>

            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="field-error"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>


        <div class="form-options">
            <label class="check-label" for="remember">
                <input
                    id="remember"
                    type="checkbox"
                    name="remember"
                    value="1"
                    <?php if(old('remember')): echo 'checked'; endif; ?>
                >
                <span>Remember me</span>
            </label>

            <?php if(Route::has('password.request')): ?>
                <a class="auth-link" href="<?php echo e(route('password.request')); ?>">
                    Forgot password?
                </a>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn-primary">
            Sign In
        </button>
    </form>

    <div class="auth-divider">
        <span></span>
        <p>Secure access to your workspace</p>
        <span></span>
    </div>

    <div class="auth-switch">
        Don't have an account?
        <a href="<?php echo e(route('auth.register')); ?>">Register here</a>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('loanengine-auth::layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\LoanEngine\packages\loanengine\auth\src/../resources/views/login.blade.php ENDPATH**/ ?>