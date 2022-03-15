@extends('viewmessage')



@section('content')
    <h1>View Message</h1>

    <table class="table" id="viewmessage">
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
        @isset($eventss)
        @foreach ($eventss as $message)
            <tr>
{{--                <td><?php echo $message->getSender()->getEmailAddress()->getName() ?></td>--}}
                <td><?php echo $message->getSubject() ?></td>
                <td><?php echo $message->getSender()->getEmailAddress()->getAddress() ?></td>
                <td><?php echo $message->getbodypreview() ?></td>
                <td><button type="button"  data-toggle="modal" data-target="#myModal" class="btn btn-success"  onclick='myFunction(<?php  echo json_encode($message); ?>)'>View</button></td>
                <td><form action="{{route('messageSend')}}" id="hidden_form" method="POST">
                        {{ csrf_field() }}
                        <input type="hidden" class="form-control" name="eventuser" value={{ $message->getSender()->getEmailAddress()->getName()}} />
                        <input type="hidden" class="form-control" name="eventsubject" value="{{$message->getSubject()}}" />
                        <input type="hidden" class="form-control" name="eventemail" value={{$message->getSender()->getEmailAddress()->getAddress()}} ' />
                        <input type="hidden" class="form-control" name="eventBody" value="{{$message->getbodypreview()}}"  />
                        <button type="submit" class="btn btn-primary">
                            send amrit
                        </button>
                </form>
                </td>
            </tr>
            @endforeach
            @endif

                            </table>
                    </form>
@endsection
                            <script>
                            function myFunction(message) {
                                console.log(message['receivedDateTime']);
                        $('#myModal').modal('show');
                        var name=message['sender']['emailAddress']['name'];
                        var subject=message['subject'];
                        console.log(subject);
                        $("#myModal .modal-body").html('username:    '+name+'<br>'+ 'Subject :'+subject+'');


                        }
                        </script>
