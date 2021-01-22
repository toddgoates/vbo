<h1>{{ $event->name }}</h1>
<p>Date: {{ $event->formatted_date }}</p>
<p>Time: {{ $event->formatted_time }}</p>
<p>Ticket Price: {{ $event->formatted_price }}</p>
<p>
    Address: 
    <address>
        {{ $event->address }}<br>
        {{ $event->city }}, {{ $event->state }} {{ $event->zip }}
    </address>
</p>
<p>{{ $event->additional_information }}</p>