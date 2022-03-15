@extends('viewmessage')

@section('content')
    <h1>Calendar</h1>
    <h2>{{ $dateRange }}</h2>
    <a class="btn btn-light btn-sm mb-3" href={{action('CalendarController@getNewEventForm')}}>New event Gurung</a>
    <table class="table">
        <thead>
        <tr>
            <th scope="col">Organizer</th>
            <th scope="col">Subject</th>
            <th scope="col">Start</th>
            <th scope="col">End</th>
        </tr>
        </thead>
        <tbody>
        @isset($events)
            @foreach($events as $event)
                <tr>
                    <td>{{ $event->getOrganizer()->getEmailAddress()->getName() }}</td>
                    <td>{{ $event->getSubject() }}</td>
                    <td>{{ \Carbon\Carbon::parse($event->getStart()->getDateTime())->format('n/j/y g:i A') }}</td>
                    <td>{{ \Carbon\Carbon::parse($event->getEnd()->getDateTime())->format('n/j/y g:i A') }}</td>
                    <td><a class="btn btn-light btn-sm mb-3" href={{action('CalendarController@getNewEventForm')}}>New event Gurung</a> </td>
                    <td><button type="button"  data-toggle="modal" data-target="#myModal" class="btn btn-success"  onclick='myFunction(<?php  echo json_encode($event); ?>)'>View</button></td>
                </tr>

            @endforeach
        @endif
        </tbody>
    </table>
@endsection


<script>
    function myFunction(event) {
        // console.log(message['receivedDateTime']);
        $('#myModal').modal('show');
        // var name=message['sender']['emailAddress']['name'];
        var subject=event['subject'];
        // console.log(subject);
        $("#myModal .modal-body").html('Subject :'+subject+'');


    }
</script>
