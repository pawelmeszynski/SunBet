<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScoreRequest;
use App\Http\Requests\UpdateScoreRequest;
use App\Models\Predict;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MatchesController extends Controller
{
    public function index(): View
    {
        return view('matches.ongoing-matches-list')->with([
            'matches' => Schedule::orderBy('matchday', 'asc')
                ->where('matchday', '!=', 'NULL')
                ->where('competition_id', '=', '2000')->whereDoesntHave('predicts', function($query) {
                    $query->where('user_id', Auth::user()->id);
                })->get()
        ]); //show matches list
    }

    public function create(StoreScoreRequest $request)
    {
        $data = $request->except('_token');
        $data = $data['matches'];

        foreach($data as $key =>$item) {
            if (($item['home_team_goals'] != null) && ($item['away_team_goals'] != null)) {
                Predict::create([
                    'match_id' => $key,
                    'user_id' => Auth::user()?->id ?? null,
                    'home_team_goals' => $item['home_team_goals'],
                    'away_team_goals' => $item['away_team_goals'],
                ]);
            }
        }

        return to_route('matches.predicts');
    }

    public function update(UpdateScoreRequest $updateScoreRequest, Predict $predict): \Illuminate\Http\RedirectResponse
    {
        $data = $updateScoreRequest->validated(); //get only validated data
        $result = $predict->update([
            'match_id' => $data['match_id'],
            'user_id' => Auth::user()?->id ?? null,
            'home_team_goals' => $data['home_team_goals'],
            'away_team_goals' => $data['away_team_goals'],
        ]); //update score with new data

        return back()->with([
            'status' => [
                'status' => $result ? 'success' : 'failed',
                'message' => $result ? 'Score succesfully edited' : 'Something went wrong, sorry',
            ],
        ]);
    }

    public function predicts()
    {
        return view('matches.predicts')->with([
            'matches' => Schedule::orderBy('matchday', 'Asc')
                ->where('matchday', '!=', 'NULL')
                ->where('competition_id', '=', '2000')->get(),
            'predicts' => Predict::all()->where('competition_id', '=', '2000'),

        ]);
    }
}
