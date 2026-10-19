<?php $__env->startSection('title', 'Tools & Calculators | LoanEngine'); ?>
<?php $__env->startSection('page_heading', 'Tools & Calculators'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/user-tools.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="le-tools">

    <section class="le-hero">
        <div>
            <span class="le-eyebrow">YOUR FINANCIAL TOOLKIT</span>
            <h1>Make clearer financial decisions.</h1>
            <p>Explore practical calculators and resources to plan your next financial step with confidence.</p>

            <form class="le-search" action="<?php echo e(route('user.tools.index')); ?>" method="GET">
                <input
                    type="search"
                    name="search"
                    value="<?php echo e($filters['search'] ?? ''); ?>"
                    placeholder="Search calculators and resources..."
                    aria-label="Search tools"
                >

                <?php if(!empty($filters['category'])): ?>
                    <input type="hidden" name="category" value="<?php echo e($filters['category']); ?>">
                <?php endif; ?>

                <?php if(($sort ?? 'recommended') !== 'recommended'): ?>
                    <input type="hidden" name="sort" value="<?php echo e($sort); ?>">
                <?php endif; ?>

                <button type="submit" class="le-btn le-btn-primary">Search →</button>
            </form>
        </div>

        <div class="le-hero-art" aria-hidden="true">
            <span>▦</span>
            <strong>Plan with clarity</strong>
            <small>Simple tools. Better decisions.</small>
            <div class="le-bars">
                <i></i><i></i><i></i><i></i><i></i>
            </div>
        </div>
    </section>

    <section>
        <div class="le-section-head">
            <div>
                <span class="le-eyebrow">EXPLORE YOUR OPTIONS</span>
                <h2>Tools built around your goals</h2>
                <p>Choose a tool to get started.</p>
            </div>

            <span class="le-count"><?php echo e($tools->total()); ?> tools available</span>
        </div>

        <form class="le-toolbar" method="GET" action="<?php echo e(route('user.tools.index')); ?>">
            <input
                type="hidden"
                name="search"
                value="<?php echo e($filters['search'] ?? ''); ?>"
            >

            <div class="le-filter-row">
                <label>
                    <span class="le-sr-only">Category</span>
                    <select name="category" onchange="this.form.submit()">
                        <option value="">All categories</option>

                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option
                                value="<?php echo e($category); ?>"
                                <?php if(($filters['category'] ?? '') === $category): echo 'selected'; endif; ?>
                            >
                                <?php echo e($category); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label>
                    <span class="le-sr-only">Sort</span>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="recommended" <?php if($sort === 'recommended'): echo 'selected'; endif; ?>>
                            Recommended
                        </option>
                        <option value="name" <?php if($sort === 'name'): echo 'selected'; endif; ?>>
                            Name A–Z
                        </option>
                        <option value="newest" <?php if($sort === 'newest'): echo 'selected'; endif; ?>>
                            Newest first
                        </option>
                    </select>
                </label>
            </div>

            <?php if(
                ($filters['search'] ?? '') !== ''
                || ($filters['category'] ?? '') !== ''
                || $sort !== 'recommended'
            ): ?>
                <a href="<?php echo e(route('user.tools.index')); ?>">Clear filters ×</a>
            <?php endif; ?>
        </form>

        <?php if($tools->isNotEmpty()): ?>
            <div class="le-grid">
                <?php $__currentLoopData = $tools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tool): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="le-card">
                        <div class="le-card-top">
                            <span class="le-icon" aria-hidden="true">
                                <?php switch($tool->icon):
                                    case ('house'): ?>
                                    <?php case ('home'): ?>
                                        ⌂
                                        <?php break; ?>

                                    <?php case ('calendar'): ?>
                                        ▦
                                        <?php break; ?>

                                    <?php case ('book'): ?>
                                        ▤
                                        <?php break; ?>

                                    <?php case ('chart'): ?>
                                        ↗
                                        <?php break; ?>

                                    <?php default: ?>
                                        ▦
                                <?php endswitch; ?>
                            </span>

                            <span class="le-chip"><?php echo e($tool->category); ?></span>
                            <span class="le-arrow">↗</span>
                        </div>

                        <div class="le-card-body">
                            <small><?php echo e($tool->type); ?></small>

                            <h3>
                                <a href="<?php echo e(route('user.tools.show', $tool->slug)); ?>">
                                    <?php echo e($tool->name); ?>

                                </a>
                            </h3>

                            <p><?php echo e($tool->short_description); ?></p>
                        </div>

                        <?php if($tool->journeys->isNotEmpty()): ?>
                            <div class="le-tags">
                                <?php $__currentLoopData = $tool->journeys->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $journey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span><?php echo e($journey->short_label ?: $journey->name); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php if($tool->journeys->count() > 2): ?>
                                    <span>+<?php echo e($tool->journeys->count() - 2); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="le-card-foot">
                            <span>● Available to use</span>

                            <a
                                class="le-btn le-btn-card"
                                href="<?php echo e(route('user.tools.show', $tool->slug)); ?>"
                            >
                                Explore tool →
                            </a>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="le-pagination">
                <?php echo e($tools->links()); ?>

            </div>
        <?php else: ?>
            <div class="le-empty">
                <span>⌕</span>
                <h3>No tools found</h3>
                <p>Try another search or clear the selected filters.</p>

                <a class="le-btn le-btn-primary" href="<?php echo e(route('user.tools.index')); ?>">
                    View all tools
                </a>
            </div>
        <?php endif; ?>
    </section>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/user/tools/index.blade.php ENDPATH**/ ?>