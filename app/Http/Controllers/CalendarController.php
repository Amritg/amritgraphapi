<?php
/*
namespace App\Http\Controllers;


    public function getNewEventForm()
    {
        $viewData = $this->loadViewData();

        return view('newevent', $viewData);
    }

    public function createNewEvent(Request $request)
    {
        // Validate required fields
        $request->validate([
            'eventSubject' => 'nullable|string',
            'eventAttendees' => 'nullable|string',
            'eventStart' => 'required|date',
            'eventEnd' => 'required|date',
            'eventBody' => 'nullable|string'
        ]);

        $viewData = $this->loadViewData();

        $graph = $this->getGraph();

        // Attendees from form are a semi-colon delimited list of
        // email addresses
        $attendeeAddresses = explode(';', $request->eventAttendees);

        // The Attendee object in Graph is complex, so build the structure
        $attendees = [];
        foreach($attendeeAddresses as $attendeeAddress)
        {
            array_push($attendees, [
                // Add the email address in the emailAddress property
                'emailAddress' => [
                    'address' => $attendeeAddress
                ],
                // Set the attendee type to required
                'type' => 'required'
            ]);
        }

        // Build the event
        $newEvent = [
            'subject' => $request->eventSubject,
            'attendees' => $attendees,
            'start' => [
                'dateTime' => $request->eventStart,
                'timeZone' => $viewData['userTimeZone']
            ],
            'end' => [
                'dateTime' => $request->eventEnd,
                'timeZone' => $viewData['userTimeZone']
            ],
            'body' => [
                'content' => $request->eventBody,
                'contentType' => 'text'
            ]
        ];

        // POST /me/events
        $response = $graph->createRequest('POST', '/me/events')
            ->attachBody($newEvent)
            ->setReturnType(Model\Event::class)
            ->execute();

        return redirect('/calendar');
    }


}
*/


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\TokenStore\TokenCache;
use App\TimeZones\TimeZones;

class CalendarController extends Controller
{

    public function amritmail()
    {

        $tokenCache = new TokenCache();

        $accessToken = $tokenCache->getAccessToken();

        //dd($accessToken);
        $accessToken = 'eyJ0eXAiOiJKV1QiLCJub25jZSI6IkwzV0hQTVlxS1hUSUZGdDh0R1ZwMG0wQ014MTAxNmJsQmlmQjRCRV9qeDQiLCJhbGciOiJSUzI1NiIsIng1dCI6ImpTMVhvMU9XRGpfNTJ2YndHTmd2UU8yVnpNYyIsImtpZCI6ImpTMVhvMU9XRGpfNTJ2YndHTmd2UU8yVnpNYyJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC8yYzVjMDBlMy05ODA1LTQwMDktYjg2Yy1mOGZmZWVmNmM0YjUvIiwiaWF0IjoxNjQ3MzQ5OTU0LCJuYmYiOjE2NDczNDk5NTQsImV4cCI6MTY0NzM1NTU4OCwiYWNjdCI6MCwiYWNyIjoiMSIsImFpbyI6IkUyWmdZUGlaMjgvZkxOaXB0TzZiY0l5TFRrYThMK09qQjFJZTlYNXJqNFVlbFgvenRRTUEiLCJhbXIiOlsicHdkIiwicnNhIl0sImFwcF9kaXNwbGF5bmFtZSI6IkdyYXBoIEV4cGxvcmVyIiwiYXBwaWQiOiJkZThiYzhiNS1kOWY5LTQ4YjEtYThhZC1iNzQ4ZGE3MjUwNjQiLCJhcHBpZGFjciI6IjAiLCJkZXZpY2VpZCI6ImE5N2VlZjQzLWQ3OTMtNDQxZS1hNjRkLWU4NTk5Y2MxYzhmNiIsImZhbWlseV9uYW1lIjoiR3VydW5nIiwiZ2l2ZW5fbmFtZSI6IkFtcml0IiwiaWR0eXAiOiJ1c2VyIiwiaXBhZGRyIjoiODAuMTk1LjcwLjEwMiIsIm5hbWUiOiJBbXJpdCBHdXJ1bmciLCJvaWQiOiI2MDNjMDQwZC1mYTM4LTQxNTgtODg2OC1hYmExZDg3OTI1M2EiLCJwbGF0ZiI6IjMiLCJwdWlkIjoiMTAwMzIwMDFERkIyMEU4MiIsInJoIjoiMC5BWE1BNHdCY0xBV1lDVUM0YlBqXzd2YkV0UU1BQUFBQUFBQUF3QUFBQUFBQUFBQnpBQlEuIiwic2NwIjoiQ2hhbm5lbE1lc3NhZ2UuU2VuZCBDaGF0LlJlYWRXcml0ZSBDaGF0TWVzc2FnZS5TZW5kIEZpbGVzLlJlYWQgRmlsZXMuUmVhZC5BbGwgRmlsZXMuUmVhZFdyaXRlIEZpbGVzLlJlYWRXcml0ZS5BbGwgTWFpbC5SZWFkIE1haWwuUmVhZEJhc2ljIE1haWwuUmVhZFdyaXRlIE1haWwuU2VuZCBNYWlsYm94U2V0dGluZ3MuUmVhZCBvcGVuaWQgcHJvZmlsZSBTaXRlcy5SZWFkLkFsbCBTaXRlcy5SZWFkV3JpdGUuQWxsIFVzZXIuUmVhZCBVc2VyLlJlYWRCYXNpYy5BbGwgVXNlci5SZWFkV3JpdGUgZW1haWwiLCJzaWduaW5fc3RhdGUiOlsia21zaSJdLCJzdWIiOiJ1M0xFZnJKNVZ6aEpYbUlKUWgxM2M3SDhvZEdlaHFIak5RaV9DUW5TdEJvIiwidGVuYW50X3JlZ2lvbl9zY29wZSI6IkVVIiwidGlkIjoiMmM1YzAwZTMtOTgwNS00MDA5LWI4NmMtZjhmZmVlZjZjNGI1IiwidW5pcXVlX25hbWUiOiJhbXJpdC5ndXJ1bmdAcm91bmRjb3JwLmNvbSIsInVwbiI6ImFtcml0Lmd1cnVuZ0Byb3VuZGNvcnAuY29tIiwidXRpIjoiYUdSZ29YNVlCa1c3NFJ6MTNlRVpBQSIsInZlciI6IjEuMCIsIndpZHMiOlsiYjc5ZmJmNGQtM2VmOS00Njg5LTgxNDMtNzZiMTk0ZTg1NTA5Il0sInhtc19zdCI6eyJzdWIiOiI4UHEtZTU5S1Y0dGFIZkJBQllkXzBxSUp1Vzk2ZE1pUEFHdXZxclJnV0o0In0sInhtc190Y2R0IjoxNTk4MjIwMjA0fQ.EPEF46SIL2ScwPZ4D5u9VB-LJbYOds4eQmK5vhAngAuj6Pytl8D48eJvtEl85tFZVq-WFwmLLwWQ_OJGH0NT2m0HHRsri7mSLl9WbTpQQNaToNQQq9jGUN7_Mh3wxI7H_bwU0y14ALztyDIUhi7iatwqeUS37YMxUFZ3xFnIrGga4AD_NmVMtyBC3EQRtYeWNDJm1-URyKKUiJrB1iLCO45DwLtsLFH7KMqs7OY1ZaNlMFcT-kVoON2dgOOREz2x1Cahi4Rl03eZZRKGHgbGtwtSvazzeW_1TjnUdhLw-mJpeo-nelSJooWHidLMpQre0JdWEhUMDsajfhellqLU2w';
        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        $ptr = $graph->createCollectionRequest("GET", "/me/messages")
            ->setReturnType(Model\Message::class)
            ->setPageSize(20);


        $eventss = $ptr->getPage();

       // dd($eventss);

        //return response()->json($events);

        //  return view('messageView', $events);

        return view('show', compact('eventss'));

    }

    public function view()
    {

        $tokenCache = new TokenCache();

        $accessToken = $tokenCache->getAccessToken();

        //dd($accessToken);
        $accessToken = 'eyJ0eXAiOiJKV1QiLCJub25jZSI6ImRtSWVpRENpMjRuNy00TjVHal9qRmVRZ0hIYVhlVEFaaG1wRTZyNHRyczQiLCJhbGciOiJSUzI1NiIsIng1dCI6ImpTMVhvMU9XRGpfNTJ2YndHTmd2UU8yVnpNYyIsImtpZCI6ImpTMVhvMU9XRGpfNTJ2YndHTmd2UU8yVnpNYyJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC8yYzVjMDBlMy05ODA1LTQwMDktYjg2Yy1mOGZmZWVmNmM0YjUvIiwiaWF0IjoxNjQ3MjYzOTE4LCJuYmYiOjE2NDcyNjM5MTgsImV4cCI6MTY0NzI2OTI3MCwiYWNjdCI6MCwiYWNyIjoiMSIsImFpbyI6IkFTUUEyLzhUQUFBQURnQTJOdG8vMEhtakdyTXFLN1N4cnhFUFVraTJ2aGt3UGhQdXkvZVU0N289IiwiYW1yIjpbInB3ZCIsInJzYSJdLCJhcHBfZGlzcGxheW5hbWUiOiJHcmFwaCBFeHBsb3JlciIsImFwcGlkIjoiZGU4YmM4YjUtZDlmOS00OGIxLWE4YWQtYjc0OGRhNzI1MDY0IiwiYXBwaWRhY3IiOiIwIiwiZGV2aWNlaWQiOiJhOTdlZWY0My1kNzkzLTQ0MWUtYTY0ZC1lODU5OWNjMWM4ZjYiLCJmYW1pbHlfbmFtZSI6Ikd1cnVuZyIsImdpdmVuX25hbWUiOiJBbXJpdCIsImlkdHlwIjoidXNlciIsImlwYWRkciI6IjgwLjE5NS43MC4xMDIiLCJuYW1lIjoiQW1yaXQgR3VydW5nIiwib2lkIjoiNjAzYzA0MGQtZmEzOC00MTU4LTg4NjgtYWJhMWQ4NzkyNTNhIiwicGxhdGYiOiIzIiwicHVpZCI6IjEwMDMyMDAxREZCMjBFODIiLCJyaCI6IjAuQVhNQTR3QmNMQVdZQ1VDNGJQal83dmJFdFFNQUFBQUFBQUFBd0FBQUFBQUFBQUJ6QUJRLiIsInNjcCI6IkNoYW5uZWxNZXNzYWdlLlNlbmQgQ2hhdC5SZWFkV3JpdGUgQ2hhdE1lc3NhZ2UuU2VuZCBGaWxlcy5SZWFkIEZpbGVzLlJlYWQuQWxsIEZpbGVzLlJlYWRXcml0ZSBGaWxlcy5SZWFkV3JpdGUuQWxsIE1haWwuUmVhZCBNYWlsLlJlYWRCYXNpYyBNYWlsLlJlYWRXcml0ZSBNYWlsLlNlbmQgTWFpbGJveFNldHRpbmdzLlJlYWQgb3BlbmlkIHByb2ZpbGUgU2l0ZXMuUmVhZC5BbGwgU2l0ZXMuUmVhZFdyaXRlLkFsbCBVc2VyLlJlYWQgVXNlci5SZWFkV3JpdGUgZW1haWwiLCJzaWduaW5fc3RhdGUiOlsia21zaSJdLCJzdWIiOiJ1M0xFZnJKNVZ6aEpYbUlKUWgxM2M3SDhvZEdlaHFIak5RaV9DUW5TdEJvIiwidGVuYW50X3JlZ2lvbl9zY29wZSI6IkVVIiwidGlkIjoiMmM1YzAwZTMtOTgwNS00MDA5LWI4NmMtZjhmZmVlZjZjNGI1IiwidW5pcXVlX25hbWUiOiJhbXJpdC5ndXJ1bmdAcm91bmRjb3JwLmNvbSIsInVwbiI6ImFtcml0Lmd1cnVuZ0Byb3VuZGNvcnAuY29tIiwidXRpIjoiQzNmZ1pub2o2VXVoYW8xZS1rMmdBQSIsInZlciI6IjEuMCIsIndpZHMiOlsiYjc5ZmJmNGQtM2VmOS00Njg5LTgxNDMtNzZiMTk0ZTg1NTA5Il0sInhtc19zdCI6eyJzdWIiOiI4UHEtZTU5S1Y0dGFIZkJBQllkXzBxSUp1Vzk2ZE1pUEFHdXZxclJnV0o0In0sInhtc190Y2R0IjoxNTk4MjIwMjA0fQ.Q1pB2RXxhANDXd0l7sW2hNIgljHSM7oQRTYqRVwJolaRftGGF6-eZ7ptvP9CS2JIWmKlxCr5rZ_W8nuek1uht_5iHMtp5r6tCWT-2EqmkC0QrsgCqIuUNob7QY_E1qPae6v64PV5k5hiJYj5_pU2Far7m1xtnDtC63lMgBRZ5TZkTBswSS-mooRhDZ5geWEsl4NX4y4bV_90U9viglsG81_dPPLqvnhG8bPFQyIYcL2koY85qe-mOs1OHQFsA-WVxC3FYVsenbPMSdI_mGhQEUjj4zMJx13feO4_9992pt5ZPabNBPNU-LxHAJBcTi3YNGb2hYPppca9qGk_bbopuw';
        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        $ptr = $graph->createCollectionRequest("GET", "/me/messages")
            ->setReturnType(Model\Message::class)
            ->setPageSize(20);


        $events = $ptr->getPage();

        //dd($eventss);

        //return response()->json($events);

        //  return view('messageView', $events);

        return view('replymail', compact('events'));

    }

    public function getNewEventForm()
    {
        $viewData = $this->loadViewData();

        return view('newevent', $viewData);
    }

    public function reply()
    {
        $viewData = $this->loadViewData();

        return view('email', $viewData);
    }

    public function calendar()
    {
        $viewData = $this->loadViewData();

        $graph = $this->getGraph();

        // Get user's timezone
        $timezone = TimeZones::getTzFromWindows($viewData['userTimeZone']);

        // Get start and end of week
        $startOfWeek = new \DateTimeImmutable('sunday -1 week', $timezone);
        $endOfWeek = new \DateTimeImmutable('sunday', $timezone);

        $viewData['dateRange'] = $startOfWeek->format('M j, Y') . ' - ' . $endOfWeek->format('M j, Y');

        $queryParams = array(
            'startDateTime' => $startOfWeek->format(\DateTimeInterface::ISO8601),
            'endDateTime' => $endOfWeek->format(\DateTimeInterface::ISO8601),
            // Only request the properties used by the app
            '$select' => 'subject,organizer,start,end',
            // Sort them by start time
            '$orderby' => 'start/dateTime',
            // Limit results to 25
            '$top' => 25
        );

        // Append query parameters to the '/me/calendarView' url
        $getEventsUrl = '/me/calendarView?' . http_build_query($queryParams);

        $events = $graph->createRequest('GET', $getEventsUrl)
            // Add the user's timezone to the Prefer header
            ->addHeaders(array(
                'Prefer' => 'outlook.timezone="' . $viewData['userTimeZone'] . '"'
            ))
            ->setReturnType(Model\Event::class)
            ->execute();
       // dd($events);
        $viewData['events'] = $events;
        return view('calendar', $viewData);
    }

    /*
      public function calendars()
      {
        $tokenCache = new TokenCache();
        $accessToken = 'eyJ0eXAiOiJKV1QiLCJub25jZSI6Ik9tcDNCVE0tNzRHODR0OTFNYmk2a2NZNWRfRnBpN2J6Q3QyMUtHX201UWciLCJhbGciOiJSUzI1NiIsIng1dCI6ImpTMVhvMU9XRGpfNTJ2YndHTmd2UU8yVnpNYyIsImtpZCI6ImpTMVhvMU9XRGpfNTJ2YndHTmd2UU8yVnpNYyJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC8yYzVjMDBlMy05ODA1LTQwMDktYjg2Yy1mOGZmZWVmNmM0YjUvIiwiaWF0IjoxNjQ2NjgxMjIzLCJuYmYiOjE2NDY2ODEyMjMsImV4cCI6MTY0NjY4NTM3MywiYWNjdCI6MCwiYWNyIjoiMSIsImFpbyI6IkFTUUEyLzhUQUFBQUZaWE5LOFRwbDJKK24xWnJlRW9ZdHAzOFEvWVR1b3kxYnZOU3ZtOHNGOWM9IiwiYW1yIjpbInB3ZCJdLCJhcHBfZGlzcGxheW5hbWUiOiJHcmFwaCBFeHBsb3JlciIsImFwcGlkIjoiZGU4YmM4YjUtZDlmOS00OGIxLWE4YWQtYjc0OGRhNzI1MDY0IiwiYXBwaWRhY3IiOiIwIiwiZmFtaWx5X25hbWUiOiJHdXJ1bmciLCJnaXZlbl9uYW1lIjoiQW1yaXQiLCJpZHR5cCI6InVzZXIiLCJpcGFkZHIiOiI2Mi4zMC4xOTkuMjMzIiwibmFtZSI6IkFtcml0IEd1cnVuZyIsIm9pZCI6IjYwM2MwNDBkLWZhMzgtNDE1OC04ODY4LWFiYTFkODc5MjUzYSIsInBsYXRmIjoiOCIsInB1aWQiOiIxMDAzMjAwMURGQjIwRTgyIiwicmgiOiIwLkFYTUE0d0JjTEFXWUNVQzRiUGpfN3ZiRXRRTUFBQUFBQUFBQXdBQUFBQUFBQUFCekFCUS4iLCJzY3AiOiJGaWxlcy5SZWFkIEZpbGVzLlJlYWQuQWxsIEZpbGVzLlJlYWRXcml0ZSBGaWxlcy5SZWFkV3JpdGUuQWxsIE1haWwuUmVhZCBNYWlsLlJlYWRCYXNpYyBNYWlsLlJlYWRXcml0ZSBNYWlsYm94U2V0dGluZ3MuUmVhZCBvcGVuaWQgcHJvZmlsZSBTaXRlcy5SZWFkLkFsbCBTaXRlcy5SZWFkV3JpdGUuQWxsIFVzZXIuUmVhZCBVc2VyLlJlYWRXcml0ZSBlbWFpbCIsInNpZ25pbl9zdGF0ZSI6WyJrbXNpIl0sInN1YiI6InUzTEVmcko1VnpoSlhtSUpRaDEzYzdIOG9kR2VocUhqTlFpX0NRblN0Qm8iLCJ0ZW5hbnRfcmVnaW9uX3Njb3BlIjoiRVUiLCJ0aWQiOiIyYzVjMDBlMy05ODA1LTQwMDktYjg2Yy1mOGZmZWVmNmM0YjUiLCJ1bmlxdWVfbmFtZSI6ImFtcml0Lmd1cnVuZ0Byb3VuZGNvcnAuY29tIiwidXBuIjoiYW1yaXQuZ3VydW5nQHJvdW5kY29ycC5jb20iLCJ1dGkiOiJ2ZXRBUFJaTFRrR3BLVERDZTZBc0FBIiwidmVyIjoiMS4wIiwid2lkcyI6WyJiNzlmYmY0ZC0zZWY5LTQ2ODktODE0My03NmIxOTRlODU1MDkiXSwieG1zX3N0Ijp7InN1YiI6IjhQcS1lNTlLVjR0YUhmQkFCWWRfMHFJSnVXOTZkTWlQQUd1dnFyUmdXSjQifSwieG1zX3RjZHQiOjE1OTgyMjAyMDR9.k6CqP6mFa8t2PZ5RNYhjfAu3AdY8tbh2Fz6_YwPBZr2VL1BCs3qBJgRLETfOWVJsTxydrdsw11O5Y3xop8WmQknrJdJIxRas-MlFi4Vhw4pKVTNtmBbWamcg9MxuLFAsXeATD2T9dgl4nrABipQmotFXAxiMzKwB5Cebv_eLtVykWb3w2AEQmcYKAaULV6YHboqbfAH9qDpOI7OinH_WUumKDqlAlkwWjszieZgZULQoE1-iLfLsA98Id1qQPTkfdRORR4c57XS9DLlgBxZK1PMuo41025MBbo5Yk9PaKKa9I9auwOeaNGi0mGX_syrjChSF_dZbqlJCj-uXZLxtJQ';
      //  dd($accessToken);

        // Create a Graph client
        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        $ptr=$graph->createCollectionRequest("GET","/me/messages")
                    ->setReturnType(Model\Message::class)
                    ->setPageSize(10);



           $msgs=$ptr->getPage();

           dd($msgs);

         return response()->json($msgs);
      }
    */

    private function getGraph(): Graph
    {
        // Get the access token from the cache
        $tokenCache = new TokenCache();
        $accessToken = $tokenCache->getAccessToken();

        // Create a Graph client
        $graph = new Graph();
        $graph->setAccessToken($accessToken);
        return $graph;
    }

    public function createNewEvent(Request $request)
    {
        // Validate required fields
        $request->validate([
            'eventSubject' => 'nullable|string',
            'eventAttendees' => 'nullable|string',
            'eventStart' => 'required|date',
            'eventEnd' => 'required|date',
            'eventBody' => 'nullable|string'
        ]);

        $viewData = $this->loadViewData();

        $graph = $this->getGraph();

        // Attendees from form are a semi-colon delimited list of
        // email addresses
        $attendeeAddresses = explode(';', $request->eventAttendees);

        // The Attendee object in Graph is complex, so build the structure
        $attendees = [];
        foreach ($attendeeAddresses as $attendeeAddress) {
            array_push($attendees, [
                // Add the email address in the emailAddress property
                'emailAddress' => [
                    'address' => $attendeeAddress
                ],
                // Set the attendee type to required
                'type' => 'required'
            ]);
        }

        // Build the event
        $newEvent = [
            'subject' => $request->eventSubject,
            'attendees' => $attendees,
            'start' => [
                'dateTime' => $request->eventStart,
                'timeZone' => $viewData['userTimeZone']
            ],
            'end' => [
                'dateTime' => $request->eventEnd,
                'timeZone' => $viewData['userTimeZone']
            ],
            'body' => [
                'content' => $request->eventBody,
                'contentType' => 'text'
            ]
        ];
        dd($newEvent);
        // POST /me/events
        $response = $graph->createRequest('POST', '/me/events')
            ->attachBody($newEvent)
            ->setReturnType(Model\Event::class)
            ->execute();

        return redirect('/calendar');
    }


    public function pleasereply(Request $request)
    {

        // Validate required fields
        $request->validate([
            'eventuser' => 'nullable|string',
            'eventemail' => 'nullable|string',
            'eventsubject' => 'nullable|string',
            'eventBody' => 'nullable|string'
            //  'eventdate' => 'nullable|string'
        ]);


        $randomNumber = rand(500, 15000000);

        $body = 'We have received your email.You support ticket number is ' . $randomNumber . '. We will get in touch with you soon. ';
        $subject = $request->eventsubject. '['.$randomNumber.']';
        //dd($subject);

        $tokenCache = new TokenCache();

        $accessToken = $tokenCache->getAccessToken();

        //dd($accessToken);
        $accessToken = 'eyJ0eXAiOiJKV1QiLCJub25jZSI6ImF3OTR0ZzBTRldjdmhZa0pvY2ZCWjVnNk56TXhvS2laYXpuT0gtOVYxRVkiLCJhbGciOiJSUzI1NiIsIng1dCI6ImpTMVhvMU9XRGpfNTJ2YndHTmd2UU8yVnpNYyIsImtpZCI6ImpTMVhvMU9XRGpfNTJ2YndHTmd2UU8yVnpNYyJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC8yYzVjMDBlMy05ODA1LTQwMDktYjg2Yy1mOGZmZWVmNmM0YjUvIiwiaWF0IjoxNjQ3MjU1NDg5LCJuYmYiOjE2NDcyNTU0ODksImV4cCI6MTY0NzI2MDczNSwiYWNjdCI6MCwiYWNyIjoiMSIsImFpbyI6IkUyWmdZTGptMWFhZ3JiWWgvZnNKRjkwMXV3U0VWdnVVQzU4N3NTNHU5Y2kzeHFMV2cvSUEiLCJhbXIiOlsicHdkIiwicnNhIl0sImFwcF9kaXNwbGF5bmFtZSI6IkdyYXBoIEV4cGxvcmVyIiwiYXBwaWQiOiJkZThiYzhiNS1kOWY5LTQ4YjEtYThhZC1iNzQ4ZGE3MjUwNjQiLCJhcHBpZGFjciI6IjAiLCJkZXZpY2VpZCI6ImE5N2VlZjQzLWQ3OTMtNDQxZS1hNjRkLWU4NTk5Y2MxYzhmNiIsImZhbWlseV9uYW1lIjoiR3VydW5nIiwiZ2l2ZW5fbmFtZSI6IkFtcml0IiwiaWR0eXAiOiJ1c2VyIiwiaXBhZGRyIjoiODAuMTk1LjcwLjEwMiIsIm5hbWUiOiJBbXJpdCBHdXJ1bmciLCJvaWQiOiI2MDNjMDQwZC1mYTM4LTQxNTgtODg2OC1hYmExZDg3OTI1M2EiLCJwbGF0ZiI6IjMiLCJwdWlkIjoiMTAwMzIwMDFERkIyMEU4MiIsInJoIjoiMC5BWE1BNHdCY0xBV1lDVUM0YlBqXzd2YkV0UU1BQUFBQUFBQUF3QUFBQUFBQUFBQnpBQlEuIiwic2NwIjoiQ2hhbm5lbE1lc3NhZ2UuU2VuZCBDaGF0LlJlYWRXcml0ZSBDaGF0TWVzc2FnZS5TZW5kIEZpbGVzLlJlYWQgRmlsZXMuUmVhZC5BbGwgRmlsZXMuUmVhZFdyaXRlIEZpbGVzLlJlYWRXcml0ZS5BbGwgTWFpbC5SZWFkIE1haWwuUmVhZEJhc2ljIE1haWwuUmVhZFdyaXRlIE1haWwuU2VuZCBNYWlsYm94U2V0dGluZ3MuUmVhZCBvcGVuaWQgcHJvZmlsZSBTaXRlcy5SZWFkLkFsbCBTaXRlcy5SZWFkV3JpdGUuQWxsIFVzZXIuUmVhZCBVc2VyLlJlYWRXcml0ZSBlbWFpbCIsInNpZ25pbl9zdGF0ZSI6WyJrbXNpIl0sInN1YiI6InUzTEVmcko1VnpoSlhtSUpRaDEzYzdIOG9kR2VocUhqTlFpX0NRblN0Qm8iLCJ0ZW5hbnRfcmVnaW9uX3Njb3BlIjoiRVUiLCJ0aWQiOiIyYzVjMDBlMy05ODA1LTQwMDktYjg2Yy1mOGZmZWVmNmM0YjUiLCJ1bmlxdWVfbmFtZSI6ImFtcml0Lmd1cnVuZ0Byb3VuZGNvcnAuY29tIiwidXBuIjoiYW1yaXQuZ3VydW5nQHJvdW5kY29ycC5jb20iLCJ1dGkiOiI4c1J1LWhPN1owNldoWmRSMl9FR0FBIiwidmVyIjoiMS4wIiwid2lkcyI6WyJiNzlmYmY0ZC0zZWY5LTQ2ODktODE0My03NmIxOTRlODU1MDkiXSwieG1zX3N0Ijp7InN1YiI6IjhQcS1lNTlLVjR0YUhmQkFCWWRfMHFJSnVXOTZkTWlQQUd1dnFyUmdXSjQifSwieG1zX3RjZHQiOjE1OTgyMjAyMDR9.BlXT4tPeoKg1QYlQHQQwIOH-1zWoWpJTxQz1PxvEGZQDonOSOz9kEZCEdJRAAY75gQjytaf9UYypEY9nlI6dgalmjazAw6ZQUDTvdAuorBQYJKc-JUrKVgNcWZn_zgZ50_xzhrTSzTZVjUrLKsXlivGISTd4yt2huSsE4I1fDFLe3NK1tH1JcLPOXbNrE7nWPom6P2YvLSrEfGTvzEi8X2jVY-rYPIRmpZn_TQholkq2dAKcWLvXeLi428lDoh9rT8eXcwdolvg-CJ7gSJB7aD0-pBZVD0TmDqlGX7Kj2KzmSf7WBuCjip1xkHbYcTkCIMscH2N3v4iJE6jZ7BuQ6g';
        $graph = new Graph();
        $graph->setAccessToken($accessToken);
        // dd($request);
        $viewData = $this->loadViewData();


        // Attendees from form are a semi-colon delimited list of
        // email addresses
       // $gomessage = explode(';', $request->eventMail);


        // Build the event

        $mailBody = array(
            "Message" => array(
                "subject" => $subject,
                "body" => array(
                    "contentType" => "html",
                    "content" => $body,
                ),
                /*"sender" => array(
                    "emailAddress" => array(
                        "name" => $request->eventuser,
                        "address" => 'amrit.gurung@roundcorp.com',
                    )
                ),
                "from" => array(
                    "emailAddress" => array(
                        "name" => $request->eventuser,
                        "address" => 'amrit.gurung@roundcorp.com',
                    )
                ),*/
              "toRecipients" => array(
                    array(
                        "emailAddress" => array(
                            "name" => $request->eventuser,
                            "address" => $request->eventemail,

                        )
                    )
                )
            )
        );


         dd($mailBody);

        // POST /me/events
        $response = $graph->createRequest('POST', '/me/sendMail')
            ->attachBody($mailBody)
            ->execute();


       // dd($response);
        return redirect('/messageA');
    }
}

