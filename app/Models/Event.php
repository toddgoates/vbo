<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dates = ['date'];

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->ticket_price / 100, 2);
    }

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F d, Y');
    }

    public function getFormattedTimeAttribute()
    {
        return $this->date->format('g:ia');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}
