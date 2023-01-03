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
  @foreach( $taskList as $task )
    <div>
      <ul>
        <li>id: {{ $task->id }}</li>
        <li>name: {{ $task->name }}</li>
        <li>position: {{ $task->position }}</li>
        <li>state: {{ $task->state }}</li>
        <li>checkListId: {{ $task->checkListId }}</li>
      </ul>
    </div>
  @endforeach

  

</body>

</html>