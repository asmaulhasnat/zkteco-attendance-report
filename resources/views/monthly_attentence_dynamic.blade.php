<table class="table">

<thead>

<tr>
    <th rowspan="2">EMP ID</th>
    <th rowspan="2" class="name-col">Name</th>
    <th rowspan="2" class="pos-col">Position</th>

    @for($d=1;$d<=$totalDays;$d++)
        <th class="day-col">{{ $d }}</th>
    @endfor

    <th rowspan="2">WK</th>
    <th colspan="{{count($leave_codes)+1}}">Leave</th>
    <th rowspan="2">Late</th>
    <th rowspan="2">Present</th>
</tr>

<tr>
    @for($d=1;$d<=$totalDays;$d++)
        @php
            $day = \Carbon\Carbon::create($year,$monthNumber,$d)->format('D');
            $short = strtolower($day);
            $class = in_array($short,$weekend) ? 'weekend' : '';
        @endphp

        <th class="{{ $class }}">{{ $short }}</th>
    @endfor
    @foreach($leave_codes ?? [] as $leave)
        <th>{{ $leave }}</th>
    @endforeach
     <th>TL</th>
</tr>

</thead>

<tbody>

@foreach($departments as $dept)

@if(count($depertment_wise_employee[$dept->id]?? [])>0 )

<tr>
    <td colspan="{{$total_colspan}}" align="left">
        {{ $dept->dept_name }}
    </td>
</tr>

@foreach($depertment_wise_employee[$dept->id] ?? [] as $employee)

<tr>
    <td>{{ $employee->emp_code ?? '' }}</td>

    <td align="left">
        {{ $employee->first_name }} {{ $employee->last_name }}
    </td>

    <td align="left">
        {{ $employee->position_name }}
    </td>

    @for($d=1;$d<=$totalDays;$d++)
        @php
            $date = \Carbon\Carbon::create($year,$monthNumber,$d)->format('Y-m-d');
            $day = \Carbon\Carbon::create($year,$monthNumber,$d)->format('D');
            $short = strtolower($day);
            $class = in_array($short,$weekend) ? 'weekend' : '';
            $weekend_value=in_array($short,$weekend) ? 'WE' : '';
        @endphp

        <td class="{{ $class }}">
            {{ 
                $attendances_by_employee[$employee->id.'__'.$date]
                ?? ($holy_days[$date] ?? $weekend_value  ?? '')
            }}
        </td>
    @endfor

    <td>{{ $working_days ?? '' }}</td>

    @foreach($leave_codes ?? [] as $leave)
        <td>
            {{ array_sum($summary_total[$employee->id.'__'.$leave] ?? [0]) }}
        </td>
    @endforeach
    <td>{{ array_sum($summary_total[$employee->id.'__TLEAVE'] ?? [0]) }}</td>

    <td>{{ array_sum($summary_total[$employee->id.'__TLATEIN'] ?? [0]) }}</td>

    <td class="present">
        {{ array_sum($summary_total[$employee->id.'__TPRESENT'] ?? [0]) }}
    </td>

</tr>

@endforeach
@endif
@endforeach

</tbody>
</table>