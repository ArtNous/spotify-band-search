<?php

namespace App\Models;

class Album {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $released;

    /**
     * @var array
     */
    protected $cover;

    /**
     * @var int
     */
    protected $tracks;

    /**
     * @var string
     */
    protected $artist;

    public function __construct($item) {
        $this->name = $item->name;
        $this->released = $item->release_date;
        $this->cover = $item->images[0];
        $this->tracks = $item->total_tracks;
    }

    public function toArray() {
        return [
            'name' => $this->name,
            'released' => $this->released,
            'tracks' => $this->tracks,
            'cover' => $this->cover,
        ];
    }

}