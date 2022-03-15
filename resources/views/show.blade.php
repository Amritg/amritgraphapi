@extends('layout')

@section('content')
    <h1>Calendar</h1>
        <table class="table">
        <thead>
        <tr>
            <th scope="col">User Name</th>
            <th scope="col">Message Title Subject</th>
            <th scope="col">Email Address</th>
            <th scope="col">Body</th>
            <th scope="col">Full details</th>
            <th scope="col">Please Reply</th>
        </tr>
        </thead>
        <tbody>
        @isset($eventss)
            @foreach($eventss as $view)
                <tr>
{{--                    <td><?php echo $view->getSender()->getEmailAddress()->getName() ?></td>--}}
                    <td><?php echo $view->getSubject() ?></td>
                    <td><button type="button"  data-toggle="modal" data-target="#myModals" class="btn btn-success"  onclick='myFunction(<?php  echo json_encode($view); ?>)'>View</button></td>
                    <td><form action="{{route('messageSend')}}" id="hidden_form" method="POST">
                            {{ csrf_field() }}

                            <input type="hidden" class="form-control" name="eventsubject" value="{{$view->getSubject()}}" />

                            <button type="submit" class="btn btn-primary">
                                send amrit
                            </button>
                        </form>
                    </td>

                </tr>
            @endforeach
        @endif
        </tbody>
    </table>
@endsection

<script>
    function myFunction(view) {
        // console.log(message['receivedDateTime']);
        $('#myModals').modal('show');
        // var name=message['sender']['emailAddress']['name'];
        var subject=view['subject'];
        // console.log(subject);
        $("#myModals .modal-body").html('Subject :'+subject+'');
    }
</script>
