@extends('layout')

@section('content')
    <h1>sending email back</h1>
    <form method="POST">
        @csrf
        <div class="form-group">
            <label>Subject Title</label>
            <input type="text" class="form-control" name="eventTitle" />
        </div>
        <div class="form-group">
            <label>email address</label>
            <input type="text" class="form-control" name="eventemail" />
        </div>

        <div class="form-group">
            <label>Body ok</label>
            <textarea type="text" class="form-control" name="eventBodyok" rows="3"></textarea>
        </div>
        <input type="submit" class="btn btn-primary mr-2" value="send" />
        <a class="btn btn-secondary" href={{ action('CalendarController@amritmail') }}>Cancel</a>
    </form>
@endsection
