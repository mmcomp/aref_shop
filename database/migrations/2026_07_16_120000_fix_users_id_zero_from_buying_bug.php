<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Data repair for the "quiz 24 changes" regression (commit 5a49cee, 2026-07-07):
 * App\Utils\Buying::completeInsertAfterBuying() inserted user_products.users_id,
 * user_video_sessions.users_id and user_quizzes.user_id as 0 instead of the real
 * buyer, for every purchase made while the bug was live. This backfills the
 * correct id from order_details.users_id (which was always populated correctly).
 *
 * Matching is done per product/quiz/video group: candidate buyers (from
 * completed orders) who don't already have a correctly-attributed row are
 * paired, oldest-first, with the broken rows for that group. A group is only
 * repaired when the number of broken rows exactly matches the number of
 * candidate buyers - anything ambiguous is left untouched and logged to
 * storage/logs/laravel.log for manual review instead of guessing.
 */
return new class extends Migration
{
    private const COMPLETED_STATUSES = ['ok', 'manual_ok'];

    public function up(): void
    {
        DB::transaction(function () {
            $this->repairUserProducts();
            $this->repairUserQuizzes();
            $this->repairUserVideoSessions();
        });
    }

    public function down(): void
    {
        // Repairs invalid users_id/user_id = 0 rows; there is no valid prior
        // state to roll back to.
    }

    private function repairUserProducts(): void
    {
        $brokenProductIds = DB::table('user_products')
            ->where('users_id', 0)
            ->distinct()
            ->pluck('products_id');

        foreach ($brokenProductIds as $productId) {
            $broken = DB::table('user_products')
                ->where('users_id', 0)
                ->where('products_id', $productId)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $alreadyCorrectUserIds = DB::table('user_products')
                ->where('products_id', $productId)
                ->where('users_id', '!=', 0)
                ->pluck('users_id');

            $candidates = DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.orders_id')
                ->whereIn('orders.status', self::COMPLETED_STATUSES)
                ->where('order_details.products_id', $productId)
                ->whereNotIn('order_details.users_id', $alreadyCorrectUserIds)
                ->orderBy('order_details.created_at')
                ->orderBy('order_details.id')
                ->select('order_details.users_id')
                ->get()
                ->unique('users_id')
                ->values();

            $this->applyMatch('user_products', 'products_id', $productId, $broken, $candidates, 'users_id');
        }
    }

    private function repairUserQuizzes(): void
    {
        $brokenQuizIds = DB::table('user_quizzes')
            ->where('user_id', 0)
            ->distinct()
            ->pluck('quiz_id');

        foreach ($brokenQuizIds as $quizId) {
            $productIds = DB::table('product_quizzes')->where('quiz_id', $quizId)->pluck('product_id');
            if ($productIds->isEmpty()) {
                Log::warning('[user_quizzes repair] no product mapped to quiz, skipping', ['quiz_id' => $quizId]);
                continue;
            }

            $broken = DB::table('user_quizzes')
                ->where('user_id', 0)
                ->where('quiz_id', $quizId)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $alreadyCorrectUserIds = DB::table('user_quizzes')
                ->where('quiz_id', $quizId)
                ->where('user_id', '!=', 0)
                ->pluck('user_id');

            $candidates = DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.orders_id')
                ->whereIn('orders.status', self::COMPLETED_STATUSES)
                ->whereIn('order_details.products_id', $productIds)
                ->whereNotIn('order_details.users_id', $alreadyCorrectUserIds)
                ->orderBy('order_details.created_at')
                ->orderBy('order_details.id')
                ->select('order_details.users_id')
                ->get()
                ->unique('users_id')
                ->values();

            $this->applyMatch('user_quizzes', 'quiz_id', $quizId, $broken, $candidates, 'user_id');
        }
    }

    private function repairUserVideoSessions(): void
    {
        $brokenSessionIds = DB::table('user_video_sessions')
            ->where('users_id', 0)
            ->distinct()
            ->pluck('video_sessions_id');

        foreach ($brokenSessionIds as $videoSessionId) {
            $productIds = DB::table('product_detail_videos')->where('video_sessions_id', $videoSessionId)->pluck('products_id');
            if ($productIds->isEmpty()) {
                Log::warning('[user_video_sessions repair] no product mapped to video session, skipping', ['video_sessions_id' => $videoSessionId]);
                continue;
            }

            $broken = DB::table('user_video_sessions')
                ->where('users_id', 0)
                ->where('video_sessions_id', $videoSessionId)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $alreadyCorrectUserIds = DB::table('user_video_sessions')
                ->where('video_sessions_id', $videoSessionId)
                ->where('users_id', '!=', 0)
                ->pluck('users_id');

            $candidates = DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.orders_id')
                ->whereIn('orders.status', self::COMPLETED_STATUSES)
                ->whereIn('order_details.products_id', $productIds)
                ->whereNotIn('order_details.users_id', $alreadyCorrectUserIds)
                ->orderBy('order_details.created_at')
                ->orderBy('order_details.id')
                ->select('order_details.users_id')
                ->get()
                ->unique('users_id')
                ->values();

            $this->applyMatch('user_video_sessions', 'video_sessions_id', $videoSessionId, $broken, $candidates, 'users_id');
        }
    }

    private function applyMatch(string $table, string $groupColumn, $groupId, $broken, $candidates, string $userColumn): void
    {
        if ($broken->count() !== $candidates->count()) {
            Log::warning("[{$table} repair] broken/candidate count mismatch, skipping group", [
                $groupColumn => $groupId,
                'broken_count' => $broken->count(),
                'candidate_count' => $candidates->count(),
            ]);
            return;
        }

        foreach ($broken as $index => $row) {
            DB::table($table)->where('id', $row->id)->update([$userColumn => $candidates[$index]->users_id]);
        }
    }
};
