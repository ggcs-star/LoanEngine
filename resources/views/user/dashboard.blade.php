
@extends('user.layouts.app')

@section('title', 'Dashboard | LoanEngine')
@section('page_heading', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user-dashboard.css') }}">
@endpush

@php
    $user = auth()->user();
    $firstName = $user ? explode(' ', trim($user->name))[0] : 'there';

    $stats = $dashboardStats ?? [];
    $educationCount = (int) ($stats['education'] ?? 0);
    $calculatorCount = (int) ($stats['calculators'] ?? 0);
    $toolsCount = (int) ($stats['tools'] ?? 0);
    $resourcesCount = (int) ($stats['resources'] ?? 0);

    $learningProgress = max(0, min(100, (int) ($learningProgress ?? 0)));
    $completedLessons = (int) ($completedLessons ?? 0);
    $totalLessons = (int) ($totalLessons ?? 0);

    $recentLessons = $recentLessons ?? collect();
    $recommendedItems = $recommendedItems ?? collect();
    $recentUpdates = $recentUpdates ?? collect();
    $upcomingEvents = $upcomingEvents ?? collect();
    $calendarEvents = $calendarEvents ?? collect();

    $calendarPayload = $calendarEvents->map(fn ($event) => [
        'id' => $event->id,
        'date' => \Illuminate\Support\Carbon::parse($event->event_date)->toDateString(),
        'title' => $event->description,
        'amount' => (float) $event->amount,
        'category' => $event->category,
        'reminder_time' => $event->reminder_time
            ? \Illuminate\Support\Carbon::parse($event->reminder_time)->format('H:i')
            : null,
        'payment_status' => $event->payment_status,
        'notes' => $event->notes,
    ])->values();
@endphp

@section('content')
<div class="le-user-dashboard">

    {{-- Hero --}}
    <section class="le-ud-hero">
        <div class="le-ud-hero-copy">
            <span class="le-ud-eyebrow">
                <span class="le-ud-eyebrow-dot"></span>
                YOUR FINANCIAL WORKSPACE
            </span>

            <p class="le-ud-greeting">Welcome back, {{ $firstName }}</p>

            <h1>Learn. Plan.<span>Move forward.</span></h1>

            <p class="le-ud-hero-description">
                Explore loan education, use practical calculators and organise
                your financial activities from one clear workspace.
            </p>

            <div class="le-ud-hero-actions">
                @if (Route::has('user.education.index'))
                    <a class="le-ud-button le-ud-button-dark"
                       href="{{ route('user.education.index') }}">
                        Explore education <span>↗</span>
                    </a>
                @endif

                @if (Route::has('user.tools.index'))
                    <a class="le-ud-button le-ud-button-light"
                       href="{{ route('user.tools.index') }}">
                        Explore loan tools <span>→</span>
                    </a>
                @elseif (Route::has('tools.index'))
                    <a class="le-ud-button le-ud-button-light"
                       href="{{ route('tools.index') }}">
                        Explore loan tools <span>→</span>
                    </a>
                @endif
            </div>

            <div class="le-ud-hero-foot">
                <span class="le-ud-status-mark">✓</span>
                <span>Your learning journey, all in one place</span>
            </div>
        </div>

        <div class="le-ud-hero-visual" aria-hidden="true">
            <div class="le-ud-visual-orbit le-ud-orbit-one"></div>
            <div class="le-ud-visual-orbit le-ud-orbit-two"></div>
            <div class="le-ud-visual-grid"></div>

            <div class="le-ud-floating-icon le-ud-floating-book">▤</div>
            <div class="le-ud-floating-icon le-ud-floating-chart">↗</div>

            <div class="le-ud-visual-card">
                <span class="le-ud-visual-label">YOUR NEXT STEP</span>
                <div class="le-ud-visual-title">Make knowledge<br>work for you.</div>
                <div class="le-ud-visual-divider"></div>
                <div class="le-ud-visual-bottom">
                    <span class="le-ud-visual-check">✓</span>
                    <span>Learn at your own pace</span>
                </div>
            </div>
        </div>

        <div class="le-ud-hero-date">
            <span class="le-ud-date-icon">▦</span>
            <div>
                <strong>{{ now()->format('D, d M Y') }}</strong>
                <span>Your workspace overview</span>
            </div>
        </div>
    </section>

    {{-- Resource counts --}}
    <section class="le-ud-stats">
        <a class="le-ud-stat-card"
           href="{{ Route::has('user.education.index') ? route('user.education.index') : '#' }}">
            <div class="le-ud-stat-top"><span class="le-ud-stat-icon">▤</span><span class="le-ud-stat-arrow">↗</span></div>
            <div class="le-ud-stat-number">{{ number_format($educationCount) }}</div>
            <div class="le-ud-stat-title">Education Topics</div>
            <div class="le-ud-stat-foot">Education content records</div>
            <span class="le-ud-stat-progress"><span style="width:{{ $educationCount ? 100 : 0 }}%"></span></span>
        </a>

        <a class="le-ud-stat-card"
           href="{{ Route::has('calculators.index') ? route('calculators.index') : (Route::has('user.tools.index') ? route('user.tools.index') : '#') }}">
            <div class="le-ud-stat-top"><span class="le-ud-stat-icon">▦</span><span class="le-ud-stat-arrow">↗</span></div>
            <div class="le-ud-stat-number">{{ number_format($calculatorCount) }}</div>
            <div class="le-ud-stat-title">Calculators</div>
            <div class="le-ud-stat-foot">Calculator records</div>
            <span class="le-ud-stat-progress"><span style="width:{{ $calculatorCount ? 100 : 0 }}%"></span></span>
        </a>

        <a class="le-ud-stat-card"
           href="{{ Route::has('user.tools.index') ? route('user.tools.index') : (Route::has('tools.index') ? route('tools.index') : '#') }}">
            <div class="le-ud-stat-top"><span class="le-ud-stat-icon">⌘</span><span class="le-ud-stat-arrow">↗</span></div>
            <div class="le-ud-stat-number">{{ number_format($toolsCount) }}</div>
            <div class="le-ud-stat-title">Loan Tools</div>
            <div class="le-ud-stat-foot">Tool records</div>
            <span class="le-ud-stat-progress"><span style="width:{{ $toolsCount ? 100 : 0 }}%"></span></span>
        </a>

        <a class="le-ud-stat-card"
           href="{{ Route::has('user.media.index') ? route('user.media.index') : '#' }}">
            <div class="le-ud-stat-top"><span class="le-ud-stat-icon">▷</span><span class="le-ud-stat-arrow">↗</span></div>
            <div class="le-ud-stat-number">{{ number_format($resourcesCount) }}</div>
            <div class="le-ud-stat-title">Learning Resources</div>
            <div class="le-ud-stat-foot">Media and lesson records</div>
            <span class="le-ud-stat-progress"><span style="width:{{ $resourcesCount ? 100 : 0 }}%"></span></span>
        </a>
    </section>

    <div class="le-ud-main-grid">
        <div class="le-ud-main-column">

            {{-- Learning progress --}}
            <section class="le-ud-panel">
                <div class="le-ud-panel-heading">
                    <div>
                        <span class="le-ud-section-label">YOUR JOURNEY</span>
                        <h2>My Learning Progress</h2>
                        <p>Explore published lessons and continue learning at your own pace.</p>
                    </div>
                    @if (Route::has('user.education.index'))
                        <a class="le-ud-text-link" href="{{ route('user.education.index') }}">Explore learning →</a>
                    @endif
                </div>

                <div class="le-ud-progress-layout">
                    <div class="le-ud-progress-summary">
                        <div class="le-ud-progress-ring" style="--le-ud-progress:{{ $learningProgress }}%">
                            <div class="le-ud-progress-ring-inner">
                                <strong>{{ $learningProgress }}%</strong>
                                <span>PROGRESS</span>
                            </div>
                        </div>

                        <h3>{{ $completedLessons > 0 ? 'Great progress!' : 'Your learning journey' }}</h3>

                        <p>
                            {{ $completedLessons }} of {{ $totalLessons }} published lessons completed
                        </p>

                        <div class="le-ud-progress-track">
                            <span style="width:{{ $learningProgress }}%"></span>
                        </div>
                    </div>

                    <div class="le-ud-progress-list">
                        @forelse ($recentLessons as $lesson)
                            <a class="le-ud-lesson-row" href="{{ route('user.education.lesson', $lesson->id) }}">
                                <span class="le-ud-lesson-state">↗</span>
                                <span class="le-ud-lesson-copy">
                                    <strong>{{ $lesson->title }}</strong>
                                    <small>{{ $lesson->is_completed ? '✓ Completed' : 'Not completed' }}</small>
                                </span>
                            </a>
                        @empty
                            <div class="le-ud-empty-state">
                                <span class="le-ud-empty-icon">▤</span>
                                <strong>Ready to keep learning?</strong>
                                <p>Your published lessons will appear here so you can continue learning.</p>
                                @if (Route::has('user.education.index'))
                                    <a href="{{ route('user.education.index') }}">Browse education →</a>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            {{-- Recommended --}}
            <section class="le-ud-panel">
                <div class="le-ud-panel-heading">
                    <div>
                        <span class="le-ud-section-label">PICK UP SOMETHING USEFUL</span>
                        <h2>Recommended for You</h2>
                        <p>Explore tools and learning resources.</p>
                    </div>
                </div>

                <div class="le-ud-recommend-grid">
                    @forelse ($recommendedItems as $item)
                        <a class="le-ud-recommend-card" href="{{ $item->url ?? '#' }}">
                            <div class="le-ud-recommend-top">
                                <span class="le-ud-recommend-icon">▤</span>
                                <span class="le-ud-type-pill">{{ ucfirst($item->type ?? 'Resource') }}</span>
                            </div>
                            <h3>{{ $item->title }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($item->description ?? '', 100) }}</p>
                            <span class="le-ud-recommend-link">Explore resource <b>→</b></span>
                        </a>
                    @empty
                        <div class="le-ud-empty-state le-ud-empty-wide">
                            <span class="le-ud-empty-icon">✳</span>
                            <strong>No recommendations connected yet</strong>
                            <p>This section will show real education topics, calculators and tools when their models are connected.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Latest updates --}}
            <section class="le-ud-panel">
                <div class="le-ud-panel-heading">
                    <div>
                        <span class="le-ud-section-label">LATEST FROM LOANENGINE</span>
                        <h2>Latest Updates</h2>
                        <p>Recently published platform information.</p>
                    </div>
                    @if (Route::has('user.news.index'))
                        <a class="le-ud-text-link" href="{{ route('user.news.index') }}">View all →</a>
                    @endif
                </div>

                <div class="le-ud-updates-list">
                    @forelse ($recentUpdates as $update)
                        <a class="le-ud-update-row" href="{{ $update->url ?? '#' }}">
                            <span class="le-ud-update-icon">▧</span>
                            <span class="le-ud-update-copy">
                                <strong>{{ $update->title }}</strong>
                                <small>{{ \Illuminate\Support\Str::limit($update->excerpt ?? $update->description ?? '', 130) }}</small>
                            </span>
                            <span class="le-ud-row-arrow">›</span>
                        </a>
                    @empty
                        <div class="le-ud-empty-state">
                            <span class="le-ud-empty-icon">▧</span>
                            <strong>No updates connected yet</strong>
                            <p>Connect your existing news or updates source to display published records here.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- Right column --}}
        <aside class="le-ud-side-column">

            {{-- Real Financial Flow Calendar --}}
            <section class="le-ud-panel le-ud-calendar-panel"
                     data-calendar-widget
                     data-events-url="{{ route('user.financial-flow.events') }}"
                     data-calendar-url="{{ route('user.financial-flow.index') }}">
                <div class="le-ud-panel-heading">
                    <div>
                        <span class="le-ud-section-label">PLAN AHEAD</span>
                        <h2>My Calendar</h2>
                        <p>Your saved financial events.</p>
                    </div>
                    <span class="le-ud-calendar-badge">▦</span>
                </div>

                <div class="le-ud-calendar-month">
                    <strong data-calendar-month-label>{{ now()->format('F Y') }}</strong>
                    <div class="le-ud-calendar-controls">
                        <button type="button" data-calendar-shift="-1" aria-label="Previous month">‹</button>
                        <button type="button" data-calendar-shift="1" aria-label="Next month">›</button>
                    </div>
                </div>

                <div class="le-ud-calendar">
                    <div class="le-ud-calendar-weekdays">
                        @foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dayName)
                            <span>{{ $dayName }}</span>
                        @endforeach
                    </div>
                    <div class="le-ud-calendar-days" data-calendar-days></div>
                </div>

                <div class="le-ud-calendar-selected" data-calendar-selected aria-live="polite">
                    Select a date to view its events.
                </div>

                <div class="le-ud-selected-events" data-selected-events>
                    <div class="le-ud-mini-empty">
                        <span>▦</span>
                        <p>Select a date to view scheduled events.</p>
                    </div>
                </div>

                <div class="le-ud-upcoming-heading">
                    <h3>Upcoming Events</h3>
                    <span>{{ $upcomingEvents->count() }} items</span>
                </div>

                <div class="le-ud-upcoming-list">
                    @forelse ($upcomingEvents as $event)
                        <button type="button"
                                class="le-ud-upcoming-row"
                                data-jump-date="{{ \Illuminate\Support\Carbon::parse($event->event_date)->toDateString() }}">
                            <span class="le-ud-upcoming-icon">▦</span>
                            <span class="le-ud-upcoming-copy">
                                <strong>{{ $event->description }}</strong>
                                <small>
                                    {{ \Illuminate\Support\Carbon::parse($event->event_date)->format('d M Y') }}
                                    @if ($event->reminder_time)
                                        · {{ \Illuminate\Support\Carbon::parse($event->reminder_time)->format('h:i A') }}
                                    @endif
                                </small>
                                <small>
                                    ₹{{ number_format((float) $event->amount, 2) }}
                                    · {{ ucfirst(str_replace('_', ' ', $event->payment_status)) }}
                                </small>
                            </span>
                            <span class="le-ud-row-arrow">›</span>
                        </button>
                    @empty
                        <div class="le-ud-mini-empty">
                            <span>▦</span>
                            <p>No upcoming events saved yet.</p>
                        </div>
                    @endforelse
                </div>

                @if (Route::has('user.financial-flow.index'))
                    <a class="le-ud-calendar-link" href="{{ route('user.financial-flow.index') }}">
                        Open Financial Flow Calendar <span>→</span>
                    </a>
                @endif
            </section>

            {{-- Quick actions --}}
            <section class="le-ud-panel">
                <div class="le-ud-panel-heading">
                    <div>
                        <span class="le-ud-section-label">SHORTCUTS</span>
                        <h2>Quick Actions</h2>
                    </div>
                </div>

                <div class="le-ud-quick-grid">
                    @if (Route::has('user.education.index'))
                        <a href="{{ route('user.education.index') }}"><span>▤</span><strong>Browse Education</strong></a>
                    @endif

                    @if (Route::has('user.tools.index'))
                        <a href="{{ route('user.tools.index') }}"><span>⌘</span><strong>Explore Loan Tools</strong></a>
                    @endif

                    @if (Route::has('user.financial-flow.index'))
                        <a href="{{ route('user.financial-flow.index') }}"><span>▦</span><strong>Financial Calendar</strong></a>
                    @endif

                    @if (Route::has('user.calculators.journeys.index'))
                        <a href="{{ route('user.calculators.journeys.index') }}"><span>↗</span><strong>Home Loan Calculators</strong></a>
                    @endif
                </div>
            </section>

            <section class="le-ud-support-card">
                <span class="le-ud-support-mark">L</span>
                <span class="le-ud-section-label">A GOOD PLACE TO START</span>
                <h2>Small steps lead to informed decisions.</h2>
                <p>Learn about a topic, plan your finances and take the next step when you're ready.</p>
                @if (Route::has('user.education.index'))
                    <a href="{{ route('user.education.index') }}">Start exploring <span>→</span></a>
                @endif
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const widget = document.querySelector('[data-calendar-widget]');
    if (!widget) return;

    const daysBox = widget.querySelector('[data-calendar-days]');
    const monthLabel = widget.querySelector('[data-calendar-month-label]');
    const selectedLabel = widget.querySelector('[data-calendar-selected]');
    const selectedEventsBox = widget.querySelector('[data-selected-events]');
    const eventsUrl = widget.dataset.eventsUrl;

    let month = new Date();
    month.setDate(1);

    let events = @json($calendarPayload);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const pad = value => String(value).padStart(2, '0');
    const dateKey = date =>
        `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

    const formatDate = key => {
        const [year, monthValue, day] = key.split('-').map(Number);
        return new Date(year, monthValue - 1, day).toLocaleDateString(undefined, {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        });
    };

    const money = amount => new Intl.NumberFormat('en-IN', {
        style: 'currency', currency: 'INR', maximumFractionDigits: 2
    }).format(Number(amount || 0));

    function safeText(value) {
        return String(value ?? '').replace(/[&<>"']/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        })[character]);
    }

    function renderSelectedEvents(key) {
        const dayEvents = events.filter(event => event.date === key);
        selectedLabel.textContent = formatDate(key);

        if (!dayEvents.length) {
            selectedEventsBox.innerHTML = `
                <div class="le-ud-mini-empty">
                    <span>▦</span><p>No events saved for this date.</p>
                </div>`;
            return;
        }

        selectedEventsBox.innerHTML = dayEvents.map(event => {
            const time = event.reminder_time
                ? new Date(`2000-01-01T${event.reminder_time}:00`).toLocaleTimeString([], {
                    hour: '2-digit', minute: '2-digit'
                })
                : 'Time not set';

            return `
                <article class="le-ud-event-detail">
                    <div class="le-ud-event-detail-top">
                        <strong>${safeText(event.title)}</strong>
                        <span class="le-ud-event-status">${safeText(event.payment_status || 'projected')}</span>
                    </div>
                    <div class="le-ud-event-meta">
                        <span>${safeText(time)}</span>
                        <span>${safeText(String(event.category || '').replaceAll('_', ' '))}</span>
                    </div>
                    <strong class="le-ud-event-amount">${money(event.amount)}</strong>
                    ${event.notes ? `<p>${safeText(event.notes)}</p>` : ''}
                </article>`;
        }).join('');
    }

    function renderCalendar() {
        const year = month.getFullYear();
        const monthIndex = month.getMonth();
        const offset = new Date(year, monthIndex, 1).getDay();
        const count = new Date(year, monthIndex + 1, 0).getDate();

        monthLabel.textContent = month.toLocaleDateString(undefined, {
            month: 'long', year: 'numeric'
        });

        daysBox.innerHTML = '';

        for (let i = 0; i < offset; i++) {
            const blank = document.createElement('span');
            blank.className = 'le-ud-calendar-blank';
            blank.setAttribute('aria-hidden', 'true');
            daysBox.appendChild(blank);
        }

        for (let day = 1; day <= count; day++) {
            const date = new Date(year, monthIndex, day);
            const key = dateKey(date);
            const hasEvents = events.some(event => event.date === key);

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'le-ud-calendar-day';
            button.dataset.calendarDate = key;
            button.textContent = String(day);

            if (date.getTime() === today.getTime()) button.classList.add('is-today');
            if (hasEvents) button.classList.add('has-event');

            button.addEventListener('click', () => {
                daysBox.querySelectorAll('.le-ud-calendar-day')
                    .forEach(dayButton => dayButton.classList.remove('is-selected'));
                button.classList.add('is-selected');
                renderSelectedEvents(key);
            });

            daysBox.appendChild(button);
        }
    }

    async function loadMonthEvents() {
        const params = new URLSearchParams({
            year: String(month.getFullYear()),
            month: String(month.getMonth() + 1)
        });

        try {
            const response = await fetch(`${eventsUrl}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });

            if (!response.ok) throw new Error('Unable to load calendar events.');

            const payload = await response.json();
            events = (payload.data || []).map(event => ({
                id: event.id,
                date: String(event.event_date).slice(0, 10),
                title: event.description,
                amount: event.amount,
                category: event.category,
                reminder_time: event.reminder_time
                    ? String(event.reminder_time).slice(0, 5)
                    : null,
                payment_status: event.payment_status,
                notes: event.notes
            }));

            renderCalendar();
            selectedEventsBox.innerHTML = `
                <div class="le-ud-mini-empty">
                    <span>▦</span><p>Select a date to view scheduled events.</p>
                </div>`;
            selectedLabel.textContent = 'Select a date to view its events.';
        } catch (error) {
            selectedLabel.textContent = 'Calendar events could not be loaded. Please try again.';
        }
    }

    widget.querySelectorAll('[data-calendar-shift]').forEach(button => {
        button.addEventListener('click', async () => {
            month.setMonth(month.getMonth() + Number(button.dataset.calendarShift));
            await loadMonthEvents();
        });
    });

    widget.querySelectorAll('[data-jump-date]').forEach(button => {
        button.addEventListener('click', async () => {
            const [year, monthValue, day] = button.dataset.jumpDate.split('-').map(Number);
            month = new Date(year, monthValue - 1, 1);
            await loadMonthEvents();

            const key = button.dataset.jumpDate;
            const target = daysBox.querySelector(`[data-calendar-date="${key}"]`);
            if (target) target.click();
        });
    });

    renderCalendar();

    // Initially show today's saved events if there are any.
    const todayKey = dateKey(today);
    if (events.some(event => event.date === todayKey)) {
        const todayButton = daysBox.querySelector(`[data-calendar-date="${todayKey}"]`);
        if (todayButton) todayButton.click();
    }
});
</script>
@endpush
