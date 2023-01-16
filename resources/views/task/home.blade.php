<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ env("APP_NAME") }}</title>
  @vite('resources/js/app.js')
</head>

<body data-bs-theme="dark">
  <div class="container">
    <h1 class="text-center mt-4 mb-4">TASKS IN TIMELINE</h1>

    <div class="calendar mb-4">

      <div class="calendar__row">
        <div class="calendar__cell calendar__cell--header">Monday</div>
        <div class="calendar__cell calendar__cell--header">Tuesday</div>
        <div class="calendar__cell calendar__cell--header">Wednesday</div>
        <div class="calendar__cell calendar__cell--header">Thursday</div>
        <div class="calendar__cell calendar__cell--header">Friday</div>
        <div class="calendar__cell calendar__cell--header">Saturday</div>
        <div class="calendar__cell calendar__cell--header">Sunday</div>
      </div>

      @foreach ($calendarData as $weekData)
        <div class="calendar__row">

          @foreach ($weekData["weekDays"] as $dayData)
            <div class="calendar__cell {!! $dayData['isPlaceholder']? 'calendar__cell--placeholder' : '' !!}">
              <div class="calendar__day">{{ $dayData['showMonth']? $dayData['monthName'] : '' }} {{ $dayData['dayNumber'] }}</div>
              {{-- {{ debug stuff }} --}}
            </div>
          @endforeach
          
          <div class="calendar__row-tasks-container">
            @foreach ($weekData["tasksData"] as $task)
              <div class="calendar__row-task {!! $task['isPlaceholder']? 'calendar__row-task--hidden' : '' !!}"
                style="width: {!! $task['weekPortion'] !!}%">
                <div class="calendar__row-task-inner"
                  data-bs-toggle="tooltip" 
                  data-bs-custom-class="custom-tooltip" 
                  data-bs-title="{{ $task['text'] }}">
                  <span class="d-inline-block text-truncate">{{ $task['text'] }}</span>
                </div>
              </div>
            @endforeach
          </div>

        </div>
      @endforeach

    </div>

  </div>
</body>

</html>