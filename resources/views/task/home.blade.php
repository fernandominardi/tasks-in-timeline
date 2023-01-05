<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>{{ env("APP_NAME") }}</title>

  <!-- Fonts -->
  <!-- ===TODO=== -->

  <!-- Styles -->
  <!-- ===TODO=== -->

</head>

<body>
  <h1>Hello, world!</h1>
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