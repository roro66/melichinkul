<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameRanking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GameRankingController extends Controller
{
    private const MAX_SCORE = 500_000;

    private const MAX_PLAY_TIME_MS = 3_600_000;

    public function index(): JsonResponse
    {
        $rankings = GameRanking::topRankings(10)
            ->get()
            ->map(function (GameRanking $ranking) {
                return [
                    'player_name' => $ranking->player_name,
                    'score' => $ranking->score,
                    'play_time' => $ranking->play_time,
                    'created_at' => $ranking->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'ranking' => $rankings,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'player_name' => ['required', 'string', 'max:10'],
            'score' => ['required', 'integer', 'min:0', 'max:' . self::MAX_SCORE],
            'play_time' => ['required', 'integer', 'min:0', 'max:' . self::MAX_PLAY_TIME_MS],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $rawName = preg_replace('/\s+/u', ' ', trim((string) $request->input('player_name')));
        $playerName = strtoupper(mb_substr($rawName, 0, 10));

        $ranking = GameRanking::create([
            'user_id' => $request->user()->id,
            'player_name' => $playerName,
            'score' => (int) $request->input('score'),
            'play_time' => (int) $request->input('play_time'),
        ]);

        $position = GameRanking::where('score', '>', $ranking->score)->count() + 1;

        $rankings = GameRanking::topRankings(10)
            ->get()
            ->map(function (GameRanking $r) {
                return [
                    'player_name' => $r->player_name,
                    'score' => $r->score,
                    'play_time' => $r->play_time,
                    'created_at' => $r->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'position' => $position,
            'ranking' => $rankings,
        ], 201);
    }
}
