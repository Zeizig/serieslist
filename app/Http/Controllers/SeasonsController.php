<?php

namespace App\Http\Controllers;

use App\Models\Season;

class SeasonsController extends Controller
{
    /**
     * Show one season page.
     *
     * @param int $seriesId
     * @param int $seasonNumber
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($seriesId, $seasonNumber)
    {
        /** @var Season $season */
        $season = Season::where('series_id', $seriesId)
            ->where('number', $seasonNumber)
            ->with([
                'series',
                'episodes',
                'episodes.seenEpisodes' => function ($query) {
                    $query->where('user_id', auth()->id());
                }
            ])
            ->first();
        $nextSeason = $season->nextSeason;

        $unseenEpisode = $season->episodes->first(function ($episode) {
            return $episode->seenEpisodes->count() === 0;
        });

        return view('seasons.show', [
            'season'     => $season,
            'nextSeason' => $nextSeason,
            'isSeen'     => $unseenEpisode === null,
        ]);
    }
}
