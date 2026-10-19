
@extends('user.layouts.app')

@section('title', 'Education | LoanEngine')
@section('page_heading', 'Education')

@section('content')
<div class="user-education-page">

    {{-- Education hero --}}
    <section class="education-hero">
        <div class="education-hero-copy">
            <span class="education-eyebrow">YOUR LEARNING SPACE</span>

            <h2>Learn something <span>valuable.</span></h2>

            <p>
                Explore educational content, build your knowledge and learn
                at your own pace.
            </p>

            <form
                class="education-search"
                method="GET"
                action="{{ route('user.education.index') }}"
            >
                <label class="education-search-field">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m16 16 4 4"></path>
                    </svg>

                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search education categories..."
                        aria-label="Search education categories"
                    >
                </label>

                <button type="submit">Search</button>
            </form>
        </div>

        {{-- Decorative illustration --}}
        <div class="education-hero-art" aria-hidden="true">
            <div class="education-art-orbit education-art-orbit-one"></div>
            <div class="education-art-orbit education-art-orbit-two"></div>

            <div class="education-art-document">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <div class="education-art-play">
                <svg viewBox="0 0 24 24">
                    <path d="m9 6 10 6-10 6z"></path>
                </svg>
            </div>

            <div class="education-art-audio">
                <svg viewBox="0 0 24 24">
                    <path d="M4 14v-3a8 8 0 0 1 16 0v3"></path>
                    <rect x="3" y="12" width="4" height="7" rx="2"></rect>
                    <rect x="17" y="12" width="4" height="7" rx="2"></rect>
                </svg>
            </div>

            <div class="education-art-books">
                <div class="education-book education-book-bottom"></div>
                <div class="education-book education-book-middle"></div>
                <div class="education-book education-book-top"></div>
            </div>

            <div class="education-art-cap">
                <div class="education-cap-top"></div>
                <div class="education-cap-base"></div>
                <span class="education-cap-tassel"></span>
            </div>

            <div class="education-art-plant">
                <span class="plant-stem"></span>
                <i class="plant-leaf leaf-one"></i>
                <i class="plant-leaf leaf-two"></i>
                <i class="plant-leaf leaf-three"></i>
                <i class="plant-leaf leaf-four"></i>
                <span class="plant-pot"></span>
            </div>
        </div>
    </section>

    {{-- Dynamic categories --}}
    <section class="education-section">

        <div class="education-section-heading">
            <div>
                <span class="education-eyebrow">EXPLORE CONTENT</span>
                <h2>Education categories</h2>
                <p>Choose a category and continue learning.</p>
            </div>

            <span class="education-result-count">
                {{ $categories->total() }}
                {{ \Illuminate\Support\Str::plural('category', $categories->total()) }}
                available
            </span>
        </div>

        <div class="education-category-grid">

            @forelse($categories as $category)

                <a
                    class="education-category-card"
                    href="{{ route('user.education.category', $category) }}"
                >
                    <div class="education-category-top">

                        {{-- Existing database thumbnail --}}
                        <div class="education-category-icon">

                            @if ($category->thumbnail_path)
                                <img
                                    src="{{ asset('storage/' . ltrim($category->thumbnail_path, '/')) }}"
                                    alt="{{ $category->name }}"
                                    loading="lazy"
                                    onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
                                >

                                <span
                                    class="education-category-fallback"
                                    hidden
                                >
                                    {{ strtoupper(substr($category->name, 0, 1)) }}
                                </span>
                            @else
                                <span class="education-category-fallback">
                                    {{ strtoupper(substr($category->name, 0, 1)) }}
                                </span>
                            @endif

                        </div>

                        <div class="education-category-copy">
                            <span class="education-eyebrow">CATEGORY</span>

                            <h3>{{ $category->name }}</h3>

                            <p>
                                {{ $category->description ?: 'Explore modules and published lessons in this category.' }}
                            </p>
                        </div>

                    </div>

                    <div class="education-category-footer">

                        <span class="education-category-count">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="5" y="3" width="14" height="18" rx="2"></rect>
                                <path d="M8 8h8M8 12h8M8 16h5"></path>
                            </svg>

                            {{ $category->published_modules_count }}

                            {{ \Illuminate\Support\Str::plural(
                                'module',
                                $category->published_modules_count
                            ) }}
                        </span>

                        <span class="education-explore">
                            Explore <b aria-hidden="true">→</b>
                        </span>

                    </div>
                </a>

            @empty

                <div class="education-empty">
                    <h3>No categories available</h3>

                    <p>
                        @if (!empty($filters['search']))
                            Try a different search term.
                        @else
                            Published learning categories will appear here.
                        @endif
                    </p>

                    @if (!empty($filters['search']))
                        <a href="{{ route('user.education.index') }}">
                            Clear search
                        </a>
                    @endif
                </div>

            @endforelse

        </div>

        <div class="education-pagination">
            {{ $categories->links() }}
        </div>

    </section>

</div>
@endsection
