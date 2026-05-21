@foreach($employees as $employee)
<table class="table @if($loop->iteration!=$loop->last) page-break @endif">

<thead>

<tr>
    <th colspan="3" align="left">Name: {{$employee->first_name ?? ''}} {{$employee->last_name ?? ''}}</th>
    <th colspan="{{count($leave_codes)}}" align="left">Id No: {{$employee->emp_code ?? ''}}</th>
</tr>
<tr>
    <th colspan="3" align="left">Designation: {{$employee->position_name  ?? ''}}</th>
    <th colspan="{{count($leave_codes)}}" align="left">Department: {{$employee->dept_name  ?? ''}}</th>
</tr>
<tr>
    <th colspan="2"> Date</th>
    <th rowspan="2"> Duration</th>
    <th colspan="{{count($leave_codes)}}">Leave Segmant</th>
</tr>
<tr>
    <th >From</th>
    <th>To</th>
    @foreach($leave_codes ?? [] as $leave)
        <th>{{ $leave }}</th>
    @endforeach
</tr>

</thead>

<tbody>

    @forelse($leaves_record_by_employee[$employee->id] ?? [] as $lrecord)

    @php
        $start_time = \Carbon\Carbon::parse($lrecord['leave_info']->start_time)->format('Y-m-d');

        $end_time = \Carbon\Carbon::parse($lrecord['leave_info']->end_time)->format('Y-m-d');
    @endphp

    <tr>
    <td align="left">{{$start_time}}</td>
    <td align="left">{{$end_time }}</td>
    <td align="center">{{$lrecord['leave_info']->leave_day ?? ''}}</td>
    @foreach($leave_codes ?? [] as $leave)
        <td align="center">{{ $lrecord['leave_code']== $leave ?($lrecord['leave_info']->leave_day ?? ''):'' }}</td>
    @endforeach
</tr>
@empty
@endforelse
</tbody>
</table>
<br>
@endforeach
