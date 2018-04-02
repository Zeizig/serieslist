<?php

namespace App\Http\Controllers;

use App\FileUploader;
use App\Models\SeriesStatusType;
use App\Repositories\SeriesRepository;
use App\Http\Requests\StoreSeries;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class SeriesController.
 *
 * @package App\Http\Controllers
 */
class SeriesController extends Controller
{
    /** @var Request */
    protected $request;
    /** @var SeriesRepository */
    protected $seriesRepository;

    /**
     * SeriesController constructor.
     *
     * @param Request $request
     * @param SeriesRepository $seriesRepository
     */
    public function __construct(Request $request, SeriesRepository $seriesRepository)
    {
        $this->request = $request;
        $this->seriesRepository = $seriesRepository;
    }

    /**
     * Show one series info page.
     *
     * @param Series $series
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Series $series)
    {
        $status = auth()->check() ? $series->statusForUser() : null;
        $statusTypes = SeriesStatusType::all();

        return view('series.show', compact('series', 'status', 'statusTypes'));
    }

    /**
     * Show all series.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $series = $this->seriesRepository->paginate();

        return view('series.index', compact('series'));
    }

    /**
     * Save a series from the request.
     *
     * @param StoreSeries $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(StoreSeries $request)
    {
        $series = $request->getInstance();

        if ($request->file('poster')) {
            $filenames = app()->make(FileUploader::class)
                              ->storeSeriesPoster($request);
            $series->poster = $filenames['filename'];
        }

        $series->save();

        if ($request->filled('seasons')) {
            foreach ($request->get('seasons') as $season) {
                $savedSeason = $series->addSeason($season);
                if (array_key_exists('episodes', $season)) {
                    foreach ($season['episodes'] as $episode) {
                        $savedSeason->addEpisode($episode);
                    }
                }
            }
        }

        return redirect("/series/{$series->id}");
    }

    /**
     * Show the create series form.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('series.admin.create');
    }

    /**
     * Update the given series with parameters from the request.
     *
     * @param StoreSeries $request
     * @param Series $series
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     */
    public function update(StoreSeries $request, Series $series)
    {
        $series = $request->getInstance($series);

        if ($request->file('poster')) {
            $filenames = app()->make(FileUploader::class)->storeSeriesPoster($request);
            $series->poster = $filenames['filename'];
        }

        $series->save();

        $oldSeasons   = $series->seasons;
        $newSeasons   = $request->get('seasons') ?: new Collection;
        $addedSeasons = new Collection;

        // TODO: Move to Series model?
        foreach ($oldSeasons as $oldSeason) {
            $shouldDelete = true;
            foreach ($newSeasons as $newSeason) {
                if ($oldSeason->number == $newSeason['number']) {
                    $oldSeason->updateEpisodes(
                        array_key_exists('episodes', $newSeason) ? $newSeason['episodes'] : []
                    );

                    $shouldDelete = false;
                    $addedSeasons->push($oldSeason->number);
                    break;
                }
            }

            if ($shouldDelete) {
                $oldSeason->delete();
            }
        }

        foreach ($newSeasons as $newSeason) {
            if ( ! $addedSeasons->contains($newSeason['number'])) {
                $series->addSeason($newSeason);
            }
        }

        return redirect("/series/{$series->id}");
    }

    /**
     * Show the edit series form.
     *
     * @param Series $series
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Series $series)
    {
        $series->load([
            'seasons',
            'seasons.episodes',
        ]);

        return view('series.admin.edit', compact('series'));
    }

    /**
     * Delete a series.
     *
     * @param Series $series
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Series $series)
    {
        $series->delete();

        return redirect('/series');
    }
}
