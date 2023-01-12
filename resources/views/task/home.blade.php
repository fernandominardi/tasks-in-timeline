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
    <?php dd($calendarData); ?>
    <h1 class="text-center mt-3 mb-3">TASKS IN TIMELINE</h1>

    <div class="calendar">

      @foreach ([1,2,3,4,5,6,7,8,9,10] as $item)
        <div class="calendar__row">

          @foreach ([1,2,3,4,5,6,7] as $item)
            <div class="calendar__cell">
              <div class="calendar__day">{{ $item+5 }}</div>
            </div>
          @endforeach

          <div class="calendar__row-inner-tasks">
            <div class="task bg-primary"
              style="width: 25%"
              data-bs-toggle="tooltip" 
              data-bs-custom-class="custom-tooltip" 
              data-bs-title="A very awesome task very awesome task very awesome task very awesome task.">
              <span class="d-inline-block text-truncate">A very awesome task very awesome task very awesome task very awesome task.</span>
            </div>
            <div class="task bg-primary" style="width: 25%"></div>
            <div class="task bg-primary" style="width: 25%"></div>
            <div class="task bg-primary" style="width: 25%"></div>
          </div>

        </div>
      @endforeach

    </div>

  </div>
</body>

</html>