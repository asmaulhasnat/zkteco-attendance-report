<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

    </head>

<body class="bg-gray-900 text-white">

<div class="container mx-auto p-6">

    <h1 class="text-2xl mb-4">Attendance Report</h1>

    <form id="reportForm" action="" method="POST" target="_blank">

        @csrf

        <input type="month" name="month" class="text-black p-2" value="{{date('Y-m')}}">

        <select name="department" class="text-black p-2">
            <option value="">Department</option>
            @foreach($departments as $d)
                <option value="{{ $d->id }}">{{ $d->dept_name }}</option>
            @endforeach
        </select>

        <select name="employee" class="text-black p-2">
            <option value="">Employee</option>
            @foreach($employees as $e)
                <option value="{{ $e->id }}">
                    {{ $e->first_name }} {{ $e->last_name }}
                </option>
            @endforeach
        </select>

        <select name="format" class="text-black p-2">
            <option value="pdf">PDF</option>
            <option value="csv">CSV</option>
           
        </select>

        <button class="bg-blue-600 px-4 py-2">Generate</button>

    </form>
    <hr>

    <div id="result"></div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>


</body>
</html>