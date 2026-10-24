<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EducationLesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonProgressController extends Controller
{
    public function complete(
        Request $request,
        EducationLesson $lesson
    ): RedirectResponse {
        abort_unless($lesson->status === 'published', 404);

        abort_unless(
            $lesson->module
                && $lesson->module->status === 'published'
                && $lesson->module->category?->is_active,
            404
        );

        $user = $request->user();

        DB::transaction(function () use ($user, $lesson) {
            $user->lessonProgress()->updateOrCreate(
                ['lesson_id' => $lesson->id],
                ['completed_at' => now()]
            );
        });

        return redirect()
            ->route('user.education.lesson', $lesson)
            ->with('success', 'Lesson marked as completed.');
    }
}
