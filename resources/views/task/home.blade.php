<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ env("APP_NAME") }}</title>
  @vite('resources/js/app.js')
</head>

<body>
  <h1>Hello, world!</h1>

  <div>
    <hr>
    <button type="button" class="btn btn-primary">Primary</button>
    <button type="button" class="btn btn-secondary">Secondary</button>
    <button type="button" class="btn btn-success">Success</button>
    <button type="button" class="btn btn-danger">Danger</button>
    <button type="button" class="btn btn-warning">Warning</button>
    <button type="button" class="btn btn-info">Info</button>
    <button type="button" class="btn btn-light">Light</button>
    <button type="button" class="btn btn-dark">Dark</button>
    <button type="button" class="btn btn-link">Link</button>
    <hr>
  </div>

  @foreach( $taskList as $id => $task )
    <div>
      <ul>
        @if( $task->isComplete )
          <li style="text-decoration: line-through;">
        @else
          <li>
        @endif
          name: {{ $task->name }}
        </li>
        {{-- <li>checkItemId: {{ $id }}</li></li> --}}
        {{-- <li>position: {{ $task->position }}</li></li> --}}
        {{-- <li>checkListId: {{ $task->checkListId }}</li></li> --}}
        {{-- <li>state: {{ $task->isComplete? 'TRUE' : 'false' }}</li></li> --}}
        <li>days: {{ $task->remainingDaysEstimate }}</li>
      </ul>
    </div>
  @endforeach
</body>

</html>