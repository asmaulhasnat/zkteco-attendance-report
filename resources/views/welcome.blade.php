<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

    {{-- Global Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <h6 class="mb-2">Please fix the following errors:</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Attendance Report -->
    <div class="card shadow-sm mb-5">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Attendance Report</h4>
        </div>

        <div class="card-body">

            <form action="" method="POST" target="_blank">
                @csrf

                <input type="hidden" name="report_type" value="attendance">

                <div class="row g-3">

                    <!-- Month -->
                    <div class="col-md-3">
                        <label class="form-label">Month</label>
                        <input
                            type="month"
                            name="month"
                            class="form-control @error('month') is-invalid @enderror"
                            value="{{ old('month', date('Y-m')) }}"
                        >

                        @error('month')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div class="col-md-3">
                        <label class="form-label">Department</label>

                        <select id="attendance_department"
                            name="department"
                            class="form-select @error('department') is-invalid @enderror"
                        >
                            <option value="">Select Department</option>

                            @foreach($departments as $d)
                                <option
                                    value="{{ $d->id }}"
                                    {{ old('department') == $d->id ? 'selected' : '' }}
                                >
                                    {{ $d->dept_name }}
                                </option>
                            @endforeach
                        </select>

                        @error('department')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Employee -->
                    <div class="col-md-3">
                        <label class="form-label">Employee</label>

                        <select
                            id="attendance_employee"
                            name="employee"
                            class="form-select @error('employee') is-invalid @enderror"
                        >
                            <option value="">Select Employee</option>
                            @include('employee_option')
                        </select>

                        @error('employee')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Format -->
                    <div class="col-md-3">
                        <label class="form-label">Format</label>

                        <select
                            name="format"
                            class="form-select @error('format') is-invalid @enderror"
                        >
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>

                        @error('format')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Button -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            Generate Attendance Report
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Leave Report -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Leave Report</h4>
        </div>

        <div class="card-body">

            <form action="" method="POST" target="_blank">
                @csrf

                <input type="hidden" name="report_type" value="leave">

                <div class="row g-3">

                    <!-- Start Date -->
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>

                        <input
                            type="date"
                            name="start_date"
                            class="form-control @error('start_date') is-invalid @enderror"
                            value="{{ old('start_date', date('Y-01-01')) }}"
                        >

                        @error('start_date')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- End Date -->
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>

                        <input
                            type="date"
                            name="end_date"
                            class="form-control @error('end_date') is-invalid @enderror"
                            value="{{ old('end_date', date('Y-12-31')) }}"
                        >

                        @error('end_date')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div class="col-md-3">
                        <label class="form-label">Department</label>

                        <select id="leave_department"
                            name="department"
                            class="form-select @error('department') is-invalid @enderror"
                        >
                            <option value="">Select Department</option>

                            @foreach($departments as $d)
                                <option
                                    value="{{ $d->id }}"
                                    {{ old('department') == $d->id ? 'selected' : '' }}
                                >
                                    {{ $d->dept_name }}
                                </option>
                            @endforeach
                        </select>

                        @error('department')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Employee -->
                    <div class="col-md-3">
                        <label class="form-label">Employee</label>

                        <select id="leave_employee"
                            name="employee"
                            class="form-select @error('employee') is-invalid @enderror"
                        >
                            <option value="">Select Employee</option>

                            @include('employee_option')
                        </select>

                        @error('employee')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Format -->
                    <div class="col-md-3">
                        <label class="form-label">Format</label>

                        <select
                            name="format"
                            class="form-select @error('format') is-invalid @enderror"
                        >
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>

                        @error('format')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Button -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            Generate Leave Report
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function bindDepartmentEmployee(deptId, empId) {

    document.getElementById(deptId).addEventListener('change', function() {

        let departmentId = this.value;
        let employeeSelect = document.getElementById(empId);

        employeeSelect.innerHTML =
            '<option value="">Loading...</option>';

        

        fetch(`/employees-by-department/${departmentId}`)
            .then(response => response.json())
            .then(data => {

                employeeSelect.innerHTML =
                    '<option value="">Select Employee</option>';

                data.forEach(employee => {
                    employeeSelect.innerHTML +=
                        `<option value="${employee.id}">
                            ${employee.emp_code} : ${employee.first_name}  ${employee.last_name}
                         </option>`;
                });
            });
    });
}

bindDepartmentEmployee(
    'attendance_department',
    'attendance_employee'
);

bindDepartmentEmployee(
    'leave_department',
    'leave_employee'
);
</script>

</body>
</html>