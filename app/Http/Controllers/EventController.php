<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function show($id)
    {
        $event = Event::published()->findOrFail($id);
        return view('event.detail', [
            'event' => $event
        ]);
    }
}
