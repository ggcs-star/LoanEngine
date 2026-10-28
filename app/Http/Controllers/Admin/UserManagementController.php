<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UserManagementController extends Controller
{
    /**
     * Check optional columns before using them in queries or views.
     */
    private function hasColumn(string $column): bool
    {
        return Schema::hasColumn('users', $column);
    }

    /**
     * Display the dedicated users management page.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'max:150'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'role' => ['nullable', 'string', 'max:50'],
            'verification' => [
                'nullable',
                'in:verified,unverified',
            ],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:from',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'in:10,15,25,50',
            ],
        ]);

        $hasRole = $this->hasColumn('role');
        $hasMobile = $this->hasColumn('mobile');
        $hasVerified = $this->hasColumn('email_verified_at');

        /*
         * Use the existing User model.
         * Default listing displays regular user accounts.
         */
        $query = User::query();

        if ($hasRole) {
            $query->where('role', 'user');
        }

        /*
         * General search:
         * Name, email, mobile and numeric user ID.
         */
        if (! empty($filters['q'])) {
            $search = trim($filters['q']);

            $query->where(function (Builder $builder) use (
                $search,
                $hasMobile
            ) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");

                if ($hasMobile) {
                    $builder->orWhere(
                        'mobile',
                        'like',
                        "%{$search}%"
                    );
                }

                if (ctype_digit($search)) {
                    $builder->orWhere('id', (int) $search);
                }
            });
        }

        /*
         * Individual field filters.
         */
        if (! empty($filters['name'])) {
            $query->where(
                'name',
                'like',
                '%' . trim($filters['name']) . '%'
            );
        }

        if (! empty($filters['email'])) {
            $query->where(
                'email',
                'like',
                '%' . trim($filters['email']) . '%'
            );
        }

        if ($hasMobile && ! empty($filters['mobile'])) {
            $query->where(
                'mobile',
                'like',
                '%' . trim($filters['mobile']) . '%'
            );
        }

        if (! empty($filters['user_id'])) {
            $query->whereKey($filters['user_id']);
        }

        /*
         * Role filter.
         * The role field must exist in the users table.
         */
        if ($hasRole && ! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        /*
         * Email verification filter.
         */
        if ($hasVerified && ! empty($filters['verification'])) {
            if ($filters['verification'] === 'verified') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        /*
         * Registration date filters.
         */
        if (! empty($filters['from'])) {
            $query->whereDate(
                'created_at',
                '>=',
                $filters['from']
            );
        }

        if (! empty($filters['to'])) {
            $query->whereDate(
                'created_at',
                '<=',
                $filters['to']
            );
        }

        /*
         * Summary statistics.
         */
        $regularUsers = User::query()
            ->when(
                $hasRole,
                fn (Builder $builder) => $builder->where('role', 'user')
            );

        $totalUsers = (clone $regularUsers)->count();

        $verifiedUsers = $hasVerified
            ? (clone $regularUsers)
                ->whereNotNull('email_verified_at')
                ->count()
            : null;

        $unverifiedUsers = $hasVerified
            ? (clone $regularUsers)
                ->whereNull('email_verified_at')
                ->count()
            : null;

        $newUsers = (clone $regularUsers)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        /*
         * Paginated database results.
         */
        $perPage = (int) ($filters['per_page'] ?? 15);

        $users = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'totalUsers' => $totalUsers,
            'verifiedUsers' => $verifiedUsers,
            'unverifiedUsers' => $unverifiedUsers,
            'newUsers' => $newUsers,
            'hasRole' => $hasRole,
            'hasMobile' => $hasMobile,
            'hasVerified' => $hasVerified,
        ]);
    }

    /**
     * Display an individual user's profile.
     */
   public function show(
    User $user,
    \App\Services\Admin\UserManagementService $userService
) {
    return view('admin.users.show', 
        $userService->getUserDetails($user)
    );
}
}
