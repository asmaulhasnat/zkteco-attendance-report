<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reports — {{ config('app.name', 'HR System') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:         #0f1117;
            --surface:    #181c27;
            --surface2:   #1e2335;
            --border:     #2a2f45;
            --border2:    #363d58;
            --text:       #e8eaf2;
            --muted:      #7b82a0;
            --accent-a:   #4f8ef7;   /* attendance — cool blue */
            --accent-l:   #34d49e;   /* leave — teal */
            --danger:     #f16464;
            --input-bg:   #13172070;
            --radius:     10px;
            --mono:       'JetBrains Mono', monospace;
            --sans:       'Inter', system-ui, sans-serif;
        }

        body {
            font-family: var(--sans);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 2.5rem 1rem 4rem;
        }

        /* ── Layout ── */
        .page {
            max-width: 860px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 2.5rem;
        }

        .page-header h1 {
            font-size: 1.35rem;
            font-weight: 600;
            letter-spacing: -.01em;
            color: var(--text);
        }

        .page-header p {
            font-size: .825rem;
            color: var(--muted);
            margin-top: .3rem;
        }

        /* ── Error banner ── */
        .error-banner {
            background: rgba(241,100,100,.1);
            border: 1px solid rgba(241,100,100,.3);
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            margin-bottom: 1.75rem;
            font-size: .825rem;
            color: var(--danger);
        }

        .error-banner ul { padding-left: 1.1rem; margin-top: .4rem; }
        .error-banner li { margin-top: .2rem; }

        /* ── Report card ── */
        .report-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .report-card-header {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .report-card-header .badge {
            font-family: var(--mono);
            font-size: .65rem;
            font-weight: 500;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: .25rem .55rem;
            border-radius: 5px;
        }

        .badge-a { background: rgba(79,142,247,.15); color: var(--accent-a); }
        .badge-l { background: rgba(52,212,158,.15); color: var(--accent-l); }

        .report-card-header h2 {
            font-size: .95rem;
            font-weight: 600;
            flex: 1;
        }

        .report-card-body { padding: 1.5rem; }

        /* ── Grid ── */
        .grid { display: grid; gap: 1rem; }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .span-full { grid-column: 1 / -1; }

        @media (max-width: 640px) {
            .grid-4, .grid-3, .grid-2 { grid-template-columns: 1fr; }
        }

        /* ── Field ── */
        .field { display: flex; flex-direction: column; gap: .4rem; }

        .field label {
            font-size: .7rem;
            font-weight: 500;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .field input,
        .field select {
            font-family: var(--sans);
            font-size: .875rem;
            color: var(--text);
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: .6rem .85rem;
            width: 100%;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            appearance: none;
            -webkit-appearance: none;
        }

        .field select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%237b82a0' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right .8rem center;
            padding-right: 2rem;
        }

        .field input:focus,
        .field select:focus {
            border-color: var(--border2);
            box-shadow: 0 0 0 3px rgba(255,255,255,.04);
        }

        .field input.is-invalid,
        .field select.is-invalid {
            border-color: rgba(241,100,100,.5);
        }

        .invalid-msg {
            font-size: .72rem;
            color: var(--danger);
        }

        /* ── Quota sub-grid ── */
        .quota-group {
            grid-column: 1 / -1;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem 1.1rem;
        }

        .quota-group-label {
            font-size: .68rem;
            font-weight: 500;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: .75rem;
        }

        .quota-inputs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .75rem;
        }

        @media (max-width: 480px) {
            .quota-inputs { grid-template-columns: repeat(2, 1fr); }
        }

        .quota-field { display: flex; flex-direction: column; gap: .3rem; }

        .quota-field label {
            font-family: var(--mono);
            font-size: .7rem;
            font-weight: 500;
            color: var(--accent-l);
        }

        .quota-field input {
            font-family: var(--mono);
            font-size: .85rem;
            color: var(--text);
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 7px;
            padding: .45rem .7rem;
            width: 100%;
            outline: none;
            transition: border-color .15s;
        }

        .quota-field input:focus { border-color: var(--accent-l); }

        /* ── Divider ── */
        .divider {
            grid-column: 1 / -1;
            height: 1px;
            background: var(--border);
            margin: .25rem 0;
        }

        /* ── Submit row ── */
        .submit-row {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .75rem;
            padding-top: .25rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-family: var(--sans);
            font-size: .825rem;
            font-weight: 500;
            padding: .6rem 1.3rem;
            border-radius: var(--radius);
            border: none;
            cursor: pointer;
            transition: opacity .15s, transform .1s;
            text-decoration: none;
        }

        .btn:active { transform: scale(.98); }
        .btn:hover  { opacity: .88; }

        .btn-a {
            background: var(--accent-a);
            color: #fff;
        }

        .btn-l {
            background: var(--accent-l);
            color: #0a1a14;
        }

        .btn svg { width: 14px; height: 14px; flex-shrink: 0; }

        /* ── select option dark fix ── */
        option { background: #1e2335; color: #e8eaf2; }
    </style>
</head>
<body>

<div class="page">

    <div class="page-header">
        <h1>Report Generator</h1>
        <p>Export attendance and leave data as PDF or CSV</p>
    </div>

    @if ($errors->any())
    <div class="error-banner">
        <strong>Fix the following before continuing:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ── Attendance Report ── --}}
    <div class="report-card">
        <div class="report-card-header">
            <span class="badge badge-a">Attendance</span>
            <h2>Monthly Attendance Report</h2>
        </div>

        <div class="report-card-body">
            <form action="" method="POST" target="_blank">
                @csrf
                <input type="hidden" name="report_type" value="attendance">

                <div class="grid grid-4">

                    <div class="field">
                        <label>Month</label>
                        <input type="month" name="month"
                            class="@error('month') is-invalid @enderror"
                            value="{{ old('month', date('Y-m')) }}">
                        @error('month')<span class="invalid-msg">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label>Department</label>
                        <select id="attendance_department" name="department"
                            class="@error('department') is-invalid @enderror">
                            <option value="">All departments</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}"
                                    {{ old('department') == $d->id ? 'selected' : '' }}>
                                    {{ $d->dept_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department')<span class="invalid-msg">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label>Employee</label>
                        <select id="attendance_employee" name="employee"
                            class="@error('employee') is-invalid @enderror">
                            <option value="">All employees</option>
                            @include('employee_option')
                        </select>
                        @error('employee')<span class="invalid-msg">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label>Format</label>
                        <select name="format" class="@error('format') is-invalid @enderror">
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                        @error('format')<span class="invalid-msg">{{ $message }}</span>@enderror
                    </div>

                    <div class="submit-row">
                        <button type="submit" class="btn btn-a">
                            <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 8h10M8 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Generate Report
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ── Leave Report ── --}}
    <div class="report-card">
        <div class="report-card-header">
            <span class="badge badge-l">Leave</span>
            <h2>Leave Summary Report</h2>
        </div>

        <div class="report-card-body">
            <form action="" method="POST" target="_blank">
                @csrf
                <input type="hidden" name="report_type" value="leave">

                <div class="grid grid-4">

                    <div class="field">
                        <label>Start Date</label>
                        <input type="date" name="start_date"
                            class="@error('start_date') is-invalid @enderror"
                            value="{{ old('start_date', date('Y-01-01')) }}">
                        @error('start_date')<span class="invalid-msg">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label>End Date</label>
                        <input type="date" name="end_date"
                            class="@error('end_date') is-invalid @enderror"
                            value="{{ old('end_date', date('Y-12-31')) }}">
                        @error('end_date')<span class="invalid-msg">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label>Department</label>
                        <select id="leave_department" name="department"
                            class="@error('department') is-invalid @enderror">
                            <option value="">All departments</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}"
                                    {{ old('department') == $d->id ? 'selected' : '' }}>
                                    {{ $d->dept_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department')<span class="invalid-msg">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label>Employee</label>
                        <select id="leave_employee" name="employee"
                            class="@error('employee') is-invalid @enderror">
                            <option value="">All employees</option>
                            @include('employee_option')
                        </select>
                        @error('employee')<span class="invalid-msg">{{ $message }}</span>@enderror
                    </div>

                    {{-- Leave quota inputs --}}
                    <div class="quota-group">
                        <div class="quota-group-label">Annual Leave Quotas (days)</div>
                        <div class="quota-inputs">
                            @foreach(['AL' => 60, 'SL' => 14, 'CL' => 10, 'ML' => 120] as $type => $default)
                            <div class="quota-field">
                                <label>{{ $type }}</label>
                                <input type="number" name="config[{{ $type }}]"
                                    value="{{ old('config.' . $type, $default) }}" min="0">
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="field">
                        <label>Format</label>
                        <select name="format" class="@error('format') is-invalid @enderror">
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                        @error('format')<span class="invalid-msg">{{ $message }}</span>@enderror
                    </div>

                    <div class="submit-row">
                        <button type="submit" class="btn btn-l">
                            <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 8h10M8 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Generate Report
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

</div>

<script>
function bindDepartmentEmployee(deptId, empId) {
    document.getElementById(deptId).addEventListener('change', function () {
        const departmentId = this.value;
        const employeeSelect = document.getElementById(empId);

        if (!departmentId) {
            employeeSelect.innerHTML = '<option value="">All employees</option>';
            return;
        }

        employeeSelect.innerHTML = '<option value="">Loading…</option>';

        fetch(`/employees-by-department/${departmentId}`)
            .then(r => r.json())
            .then(data => {
                employeeSelect.innerHTML = '<option value="">All employees</option>';
                data.forEach(emp => {
                    employeeSelect.innerHTML +=
                        `<option value="${emp.id}">${emp.emp_code} · ${emp.first_name} ${emp.last_name}</option>`;
                });
            })
            .catch(() => {
                employeeSelect.innerHTML = '<option value="">Failed to load</option>';
            });
    });
}

bindDepartmentEmployee('attendance_department', 'attendance_employee');
bindDepartmentEmployee('leave_department',      'leave_employee');
</script>

</body>
</html>