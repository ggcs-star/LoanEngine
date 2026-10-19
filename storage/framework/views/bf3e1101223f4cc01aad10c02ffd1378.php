<?php $__env->startSection('title', $lesson->title . ' | Education'); ?>
<?php $__env->startSection('page_heading', 'Education'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/user-education.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $mediaUrl = $lesson->media_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($lesson->media_path)
        : $lesson->external_media_url;

    $thumbnailUrl = $lesson->thumbnail_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($lesson->thumbnail_path)
        : null;

    $type = strtolower($lesson->content_type ?? 'article');
    $isVideo = in_array($type, ['video', 'reel'], true);
    $isReel = $type === 'reel';

    $currentIndex = $siblings->search(
        fn ($item) => (int) $item->id === (int) $lesson->id
    );

    $lessonNumber = $currentIndex === false ? 1 : $currentIndex + 1;
    $lessonTotal = $siblings->count();
    $progress = $lessonTotal > 0
        ? (int) round(($lessonNumber / $lessonTotal) * 100)
        : 0;

    $youtubeId = null;

    if ($isVideo && $lesson->external_media_url) {
        $host = strtolower(parse_url($lesson->external_media_url, PHP_URL_HOST) ?? '');
        $path = trim(parse_url($lesson->external_media_url, PHP_URL_PATH) ?? '', '/');

        if ($host === 'youtu.be' || str_ends_with($host, '.youtu.be')) {
            $youtubeId = explode('/', $path)[0] ?? null;
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            parse_str(parse_url($lesson->external_media_url, PHP_URL_QUERY) ?? '', $youtubeQuery);

            $youtubeId = $youtubeQuery['v'] ?? null;

            if (!$youtubeId && str_starts_with($path, 'embed/')) {
                $youtubeId = explode('/', substr($path, 6))[0] ?? null;
            }

            if (!$youtubeId && str_starts_with($path, 'shorts/')) {
                $youtubeId = explode('/', substr($path, 7))[0] ?? null;
            }
        }

        if ($youtubeId && !preg_match('/^[a-zA-Z0-9_-]{6,20}$/', $youtubeId)) {
            $youtubeId = null;
        }
    }
?>

<div class="user-education-page lesson-page">

    <a class="education-back-link lesson-back-link"
       href="<?php echo e(route('user.education.module', $lesson->module)); ?>">
        <span aria-hidden="true">←</span>
        Back to <?php echo e($lesson->module->title); ?>

    </a>

    <div class="lesson-workspace">

        <main class="lesson-main-column">

            <section class="lesson-heading-panel">
                <div class="lesson-heading-top">
                    <div class="lesson-heading-copy">
                        <div class="education-eyebrow">
                            <?php echo e($lesson->module->category->name ?? 'Learning Centre'); ?>

                            <span aria-hidden="true">/</span>
                            <?php echo e($lesson->module->title); ?>

                        </div>

                        <h2><?php echo e($lesson->title); ?></h2>

                        <p>
                            <?php echo e($lesson->description ?: 'Learn at your own pace with this lesson.'); ?>

                        </p>
                    </div>

                    <span class="lesson-type-badge">
                        <?php switch($type):
                            case ('video'): ?> Video lesson <?php break; ?>
                            <?php case ('reel'): ?> Reel <?php break; ?>
                            <?php case ('audio'): ?> Audio lesson <?php break; ?>
                            <?php default: ?> Article
                        <?php endswitch; ?>
                    </span>
                </div>

                <div class="lesson-meta-row">
                    <?php if($lesson->duration_seconds > 0): ?>
                        <span class="lesson-meta-chip">
                            <span aria-hidden="true">◷</span>
                            <?php echo e($lesson->formatted_duration); ?>

                        </span>
                    <?php endif; ?>

                    <span class="lesson-meta-chip">
                        Lesson <?php echo e($lessonNumber); ?> of <?php echo e($lessonTotal); ?>

                    </span>

                    <span class="lesson-meta-chip">
                        <?php echo e(ucfirst($type)); ?>

                    </span>
                </div>
            </section>

            <section class="lesson-player-panel" aria-label="Lesson media player">

                <?php if($mediaUrl && $isVideo && $youtubeId): ?>
                    <div class="lesson-video-frame">
                        <iframe
                            src="https://www.youtube-nocookie.com/embed/<?php echo e($youtubeId); ?>"
                            title="<?php echo e($lesson->title); ?>"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                            allowfullscreen
                            loading="lazy">
                        </iframe>
                    </div>

                <?php elseif($mediaUrl && $isVideo && !$youtubeId): ?>
                    <div class="<?php echo e($isReel ? 'lesson-reel-frame' : 'lesson-video-frame'); ?>">
                        <video
                            controls
                            playsinline
                            preload="metadata"
                            <?php if($thumbnailUrl): ?> poster="<?php echo e($thumbnailUrl); ?>" <?php endif; ?>>
                            <source src="<?php echo e($mediaUrl); ?>">
                            Your browser does not support video playback.
                        </video>
                    </div>

                <?php elseif($mediaUrl && $type === 'audio'): ?>
                    <div class="lesson-audio-player">
                        <div class="lesson-audio-art">
                            <?php if($thumbnailUrl): ?>
                                <img src="<?php echo e($thumbnailUrl); ?>"
                                     alt="<?php echo e($lesson->title); ?>"
                                     onerror="this.style.display='none';">
                            <?php else: ?>
                                <span aria-hidden="true">♫</span>
                            <?php endif; ?>
                        </div>

                        <div class="lesson-audio-label">NOW PLAYING</div>
                        <h3><?php echo e($lesson->title); ?></h3>

                        <p>
                            <?php echo e($lesson->description ?: 'Listen to this lesson at your own pace.'); ?>

                        </p>

                        <audio controls preload="metadata">
                            <source src="<?php echo e($mediaUrl); ?>">
                            Your browser does not support audio playback.
                        </audio>

                        <small class="lesson-audio-help">
                            Use the player controls to play, pause, seek and adjust volume.
                        </small>
                    </div>

                <?php elseif($type === 'article'): ?>
                    <div class="lesson-article-cover">
                        <div class="lesson-article-symbol">Aa</div>
                        <span>READING MATERIAL</span>
                        <h3><?php echo e($lesson->title); ?></h3>
                        <p><?php echo e($lesson->description ?: 'Read the lesson below.'); ?></p>
                    </div>

                <?php elseif($thumbnailUrl): ?>
                    <div class="lesson-fallback-cover">
                        <img src="<?php echo e($thumbnailUrl); ?>" alt="<?php echo e($lesson->title); ?>">
                        <div class="lesson-fallback-caption">
                            <strong><?php echo e($lesson->title); ?></strong>
                            <span>Media is not available to play.</span>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="lesson-no-media">
                        <div class="lesson-article-symbol">
                            <?php echo e($type === 'audio' ? '♫' : ($isReel ? '▷' : '▶')); ?>

                        </div>
                        <h3>Learning material</h3>
                        <p>The media file has not been added or is not available.</p>
                    </div>
                <?php endif; ?>

            </section>

            <section class="lesson-overview-panel">
                <div class="lesson-section-heading">
                    <span class="education-eyebrow">LESSON OVERVIEW</span>
                    <h3>About this lesson</h3>
                </div>

                <p class="lesson-overview-description">
                    <?php echo e($lesson->description ?: 'Explore the lesson material and learn at your own pace.'); ?>

                </p>

                <?php if($lesson->content): ?>
                    <div class="lesson-content-text">
                        <?php echo nl2br(e($lesson->content)); ?>

                    </div>
                <?php endif; ?>

                <div class="lesson-overview-stats">
                    <?php if($lesson->duration_seconds > 0): ?>
                        <div>
                            <strong><?php echo e($lesson->formatted_duration); ?></strong>
                            <span>Duration</span>
                        </div>
                    <?php endif; ?>

                    <div>
                        <strong><?php echo e($lessonNumber); ?> of <?php echo e($lessonTotal); ?></strong>
                        <span>Lesson</span>
                    </div>

                    <div>
                        <strong><?php echo e(ucfirst($type)); ?></strong>
                        <span>Content type</span>
                    </div>
                </div>
            </section>

            <nav class="lesson-navigation" aria-label="Lesson navigation">
                <?php if($previousLesson): ?>
                    <a class="lesson-nav-button lesson-nav-previous"
                       href="<?php echo e(route('user.education.lesson', $previousLesson)); ?>">
                        <span aria-hidden="true">←</span>
                        <span>
                            <small>PREVIOUS</small>
                            <strong>Previous lesson</strong>
                        </span>
                    </a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>

                <?php if($nextLesson): ?>
                    <a class="lesson-nav-button lesson-nav-next"
                       href="<?php echo e(route('user.education.lesson', $nextLesson)); ?>">
                        <span>
                            <small>NEXT LESSON</small>
                            <strong>Continue learning</strong>
                        </span>
                        <span aria-hidden="true">→</span>
                    </a>
                <?php else: ?>
                    <span class="lesson-complete-label">You reached the last lesson.</span>
                <?php endif; ?>
            </nav>

        </main>

        <aside class="lesson-playlist-panel">
            <div class="lesson-playlist-heading">
                <div>
                    <span class="education-eyebrow">YOUR LEARNING PATH</span>
                    <h3>Lesson contents</h3>
                </div>
                <span class="lesson-playlist-count">
                    <?php echo e($lessonNumber); ?>/<?php echo e($lessonTotal); ?>

                </span>
            </div>

            <div class="lesson-progress-track"
                 role="progressbar"
                 aria-label="Lesson progress"
                 aria-valuemin="0"
                 aria-valuemax="100"
                 aria-valuenow="<?php echo e($progress); ?>">
                <span style="width: <?php echo e($progress); ?>%"></span>
            </div>

            <div class="lesson-playlist-module">
                <?php echo e($lesson->module->title); ?>

            </div>

            <div class="lesson-playlist-items">
                <?php $__currentLoopData = $siblings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $itemType = strtolower($item->content_type ?? 'article');

                        $itemIcon = match ($itemType) {
                            'video' => '▶',
                            'reel' => '▷',
                            'audio' => '♫',
                            default => 'Aa',
                        };
                    ?>

                    <a class="lesson-playlist-item <?php echo e((int) $item->id === (int) $lesson->id ? 'is-current' : ''); ?>"
                       href="<?php echo e(route('user.education.lesson', $item)); ?>"
                       <?php if((int) $item->id === (int) $lesson->id): ?> aria-current="page" <?php endif; ?>>

                        <span class="lesson-playlist-icon" aria-hidden="true">
                            <?php echo e($itemIcon); ?>

                        </span>

                        <span class="lesson-playlist-copy">
                            <strong><?php echo e($item->title); ?></strong>
                            <small>
                                <?php echo e(ucfirst($itemType)); ?>

                                <?php if($item->duration_seconds > 0): ?>
                                    · <?php echo e($item->formatted_duration); ?>

                                <?php endif; ?>
                            </small>
                        </span>

                        <span class="lesson-playlist-status" aria-hidden="true">
                            <?php echo e((int) $item->id === (int) $lesson->id ? '●' : '→'); ?>

                        </span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="lesson-playlist-footer">
                <span class="lesson-playlist-footer-icon" aria-hidden="true">L</span>
                <div>
                    <strong>Keep learning</strong>
                    <p>One lesson at a time. Continue whenever you are ready.</p>
                </div>
            </div>
        </aside>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/user/education/lesson.blade.php ENDPATH**/ ?>