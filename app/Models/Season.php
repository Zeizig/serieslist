<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class Season.
 *
 * @property int id
 * @property int number
 * @property int series_id
 * @property Series series
 * @property Episode[]|Collection episodes
 *
 * @package App\Models
 */
class Season extends Model
{
    protected $fillable = ['number'];

    /**
     * Register model event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($season) {
            Episode::where('season_id', $season->id)
                ->delete();
        });
    }

    /**
     * Construct a string path for a season.
     *
     * @return string
     */
    public function path()
    {
        return "/series/{$this->series->id}/seasons/{$this->number}";
    }

    /**
     * Add an episode to this season.
     *
     * @param array $episode
     *
     * @return Episode
     */
    public function addEpisode($episode)
    {
        return $this->episodes()->create($episode);
    }

    public function updateEpisodes($episodes)
    {
        $oldEpisodes = $this->episodes;
        $updatedEpisodes = new Collection;

        foreach ($oldEpisodes as $oldEpisode) {
            $shouldDelete = true;
            foreach ($episodes as $newEpisode) {
                if ($oldEpisode->number == $newEpisode['number']) {

                    $oldEpisode->title = $newEpisode['title'];

                    $oldEpisode->save();

                    $updatedEpisodes->push($oldEpisode->number);
                    $shouldDelete = false;
                    break;
                }
            }

            if ($shouldDelete) {
                $oldEpisode->delete();
            }
        }

        foreach ($episodes as $episode) {
            if (!$updatedEpisodes->contains($episode['number'])) {
                $this->addEpisode($episode);
            }
        }
    }

    /**
     * Many to one relationship where a series has many seasons.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    /**
     * One to many relationship where a season consists of many episodes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }
}
