

<?php $__env->startSection('title', 'User Dashboard'); ?>
<?php $__env->startSection('page_heading', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>

    
    <section class="welcome-row">

        <div class="welcome-copy">

            <span class="section-kicker">YOUR LOAN WORKSPACE</span>

            <h2>
                Everything you need.<br>
                One clear view.
            </h2>

            <p>
                Learn about loans, explore useful calculators and find
                resources to help you make informed decisions.
            </p>

            <div class="welcome-actions">

                <?php if(Route::has('education.index')): ?>
                    <a href="<?php echo e(route('user.education.index')); ?>"
                       class="user-button user-button-dark">
                        Explore Education <span>→</span>
                    </a>
                <?php endif; ?>

                <?php if(Route::has('user.tools.index')): ?>
                    <a href="<?php echo e(route('user.tools.index')); ?>"
                       class="user-button user-button-light">
                        Explore Tools <span>→</span>
                    </a>
                <?php elseif(Route::has('tools.index')): ?>
                    <a href="<?php echo e(route('tools.index')); ?>"
                       class="user-button user-button-light">
                        Explore Tools <span>→</span>
                    </a>
                <?php endif; ?>

            </div>
        </div>

        <div class="welcome-art">
            <div class="art-orbit orbit-one"></div>
            <div class="art-orbit orbit-two"></div>

            <div class="art-card">
                <span class="art-card-label">YOUR NEXT STEP</span>

                <span class="art-card-title">
                    Learn. Plan.<br>
                    Move forward.
                </span>

                <span class="art-card-line"></span>

                <span class="art-card-foot">
                    A smarter financial journey
                </span>
            </div>

            <span class="art-dot dot-one"></span>
            <span class="art-dot dot-two"></span>
        </div>

    </section>


    
    <section class="section-block">

        <div class="section-heading">
            <div>
                <span class="section-kicker">GET STARTED</span>
                <h2>Explore your workspace</h2>
            </div>

            <span class="section-note">
                Useful resources, all in one place
            </span>
        </div>

        <div class="user-feature-grid">

            
            <a class="user-feature-card"
                href="<?php echo e(route('user.education.index')); ?>">

                <span class="feature-icon">▤</span>
                <span class="feature-tag">LEARN</span>

                <h3>Education</h3>

                <p>
                    Build your understanding of loans and financial essentials.
                </p>

                <span class="feature-link">
                    Browse education <b>↗</b>
                </span>
            </a>


            
            <a class="user-feature-card"
               href="<?php echo e(Route::has('user.tools.index')
                    ? route('user.tools.index')
                    : (Route::has('tools.index') ? route('tools.index') : '#')); ?>">

                <span class="feature-icon">⌘</span>
                <span class="feature-tag">CALCULATE</span>

                <h3>Loan Tools</h3>

                <p>
                    Explore available calculators and practical planning tools.
                </p>

                <span class="feature-link">
                    Explore tools <b>↗</b>
                </span>
            </a>


            
            <a class="user-feature-card"
               href="<?php echo e(Route::has('user.media.index') ? route('user.media.index') : '#'); ?>">

                <span class="feature-icon">▷</span>
                <span class="feature-tag">DISCOVER</span>

                <h3>Learning Resources</h3>

                <p>
                    Find lessons and media to continue learning at your pace.
                </p>

                <span class="feature-link">
                    View resources <b>↗</b>
                </span>
            </a>


            
            <a class="user-feature-card"
               href="<?php echo e(Route::has('user.news.index') ? route('user.news.index') : '#'); ?>">

                <span class="feature-icon">▧</span>
                <span class="feature-tag">STAY INFORMED</span>

                <h3>Latest Updates</h3>

                <p>
                    Keep up with new content and helpful platform updates.
                </p>

                <span class="feature-link">
                    See updates <b>↗</b>
                </span>
            </a>

        </div>
    </section>


    
    <section class="bottom-note">

        <div class="note-symbol">L</div>

        <div>
            <span class="section-kicker">A GOOD PLACE TO START</span>

            <h3>Small steps lead to informed decisions.</h3>

            <p>
                Explore a topic, use a calculator, and take the next step
                when you're ready.
            </p>
        </div>

    </section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('user.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/user/dashboard.blade.php ENDPATH**/ ?>