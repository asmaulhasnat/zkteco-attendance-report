<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Pdf;
use App\Services\Exports\ExportFromHtml;


class AttendanceReportController extends Controller
{

    public function index()
    {
        $data = [];

        $data['departments'] = DB::table('personnel_department')
            ->select('id', 'dept_name')
            ->get();

        $data['employees'] = DB::table('personnel_employee')
            ->select('first_name', 'last_name', 'id')
            ->get();

        return view('welcome', $data);
    }

    public function generateAbb($stri)
{
    $words = preg_split('/\s+/', trim($stri));

    // Single word case
    if (count($words) === 1 && strlen($words[0]) <= 4) {
        return strtoupper($words[0]);
    }

    $avalue = collect($words)
        ->map(function ($word) {
            return strtoupper(substr($word, 0, 1));
        })
        ->implode('.');

    return $avalue;
}

    public function monthlyAttendanceReport(Request $request)
    {

        $month = $request->month;

        $employe = $request->employee ?? null;
        $department = $request->department ?? null;

        $data = [];

        /*
        |--------------------------------------------------------------------------
        | Month Information
        |--------------------------------------------------------------------------
        */

        $date = Carbon::createFromFormat('Y-m', $month);

        $data['monthNumber'] = $monthNumber = $date->month;
        $data['monthName'] = $monthName = $date->format('F');
        $data['shortMonth'] = $shortMonth = $date->format('M');
        $data['year'] = $year = $date->year;

        $data['startDate'] = $startDate = $date->copy()
            ->startOfMonth()
            ->format('Y-m-d');

        $data['endDate'] = $endDate = $date->copy()
            ->endOfMonth()
            ->format('Y-m-d');

        $data['totalDays'] = $date->daysInMonth;

        /*
        |--------------------------------------------------------------------------
        | Departments
        |--------------------------------------------------------------------------
        */

        $departments = DB::table('personnel_department')
            ->when($department, function ($query) use ($department) {
                $query->where('id', $department);
            })
            ->get();

        $data['departments'] = $departments;

        /*
        |--------------------------------------------------------------------------
        | Holidays
        |--------------------------------------------------------------------------
        */

        $holy_days = DB::table('att_holiday')
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->get();

        $holidayDates = [];

        foreach ($holy_days as $holiday) {

            /*
            |--------------------------------------------------------------------------
            | Alias Generate
            |--------------------------------------------------------------------------
            */
            $abbr ='';
            if (!empty($holiday->alias)) {
                $abbr =$this->generateAbb($holiday->alias);
            }

            /*
            |--------------------------------------------------------------------------
            | Date Range
            |--------------------------------------------------------------------------
            */

            $period = CarbonPeriod::create(
                Carbon::parse($holiday->start_date),
                Carbon::parse($holiday->end_date)
            );

            foreach ($period as $holidayDate) {

                if (
                    $holidayDate->between(
                        Carbon::parse($startDate),
                        Carbon::parse($endDate)
                    )
                ) {

                    $holidayDates[$holidayDate->format('Y-m-d')] = $abbr;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Employees
        |--------------------------------------------------------------------------
        */

        $employees = DB::table('personnel_employee as pe')
            ->join('personnel_position as pp', 'pp.id', '=', 'pe.position_id')
            ->select(
                'pe.id',
                'pe.department_id',
                'pe.emp_code',
                'pe.first_name',
                'pe.last_name',
                'pe.nickname',
                'pe.email',
                'pp.position_name'
            )
            ->when($employe, function ($query) use ($employe) {
                $query->where('pe.id', $employe);
            })
            ->when($department, function ($query) use ($department) {
                $query->where('pe.department_id', $department);
            })
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Department Wise Employee
        |--------------------------------------------------------------------------
        */

        $depertment_wise_employee = [];

        foreach ($employees as $value) {

            $depertment_wise_employee[$value->department_id][] = $value;
        }

        /*
        |--------------------------------------------------------------------------
        | Attendance
        |--------------------------------------------------------------------------
        */

        $leave_codes  =[];

        $leave_code_infos = DB::table('att_paycode')->where('is_work',0)->get();

        foreach($leave_code_infos as $key=>$value){
            $abbr = trim($value->symbol ?? '') !== ''? $this->generateAbb($value->symbol): (trim($value->code ?? '') !== ''? $this->generateAbb($value->code): '');
            $leave_codes[]=$abbr;
        }

        $attendances = DB::table('att_payloadpaycode as aplc')
            ->selectRaw('
                aplc.emp_id as emp_id,
                aplc.att_date,
                MIN(apc.code) as pay_code_code,
                MIN(apc.symbol) as pay_code_symbol,
                MIN(apc.code_type) as pay_code_code_type,
                MIN(apc.fixed_code) as pay_code_fixed_code,
                apc.is_work as is_working
            ')
            ->join('att_paycode as apc', 'apc.id', '=', 'aplc.pay_code_id')
            ->whereBetween('aplc.att_date', [$startDate, $endDate]);

        if ($employe) {

            $attendances = $attendances
                ->where('aplc.emp_id', $employe);
        }

        $attendances = $attendances
            ->groupBy('aplc.emp_id', 'aplc.att_date','apc.is_work')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Attendance Summary
        |--------------------------------------------------------------------------
        */

        $attendances_by_employee = [];
        $summary_total = [];

        foreach ($attendances as $value) {

            $day = Carbon::parse($value->att_date)->format('D');

            $short = strtoupper(substr($day, 0, 2));

            $is_weekend = in_array($short, ['SU', 'SA']);


            if( $is_weekend &&  !$value->is_working){
                continue;
            }

            if (in_array($value->att_date,$holidayDates)){
                continue;
            }
            $avalue = trim($value->pay_code_symbol ?? '') !== ''? $this->generateAbb($value->pay_code_symbol): (trim($value->pay_code_code ?? '') !== ''? $this->generateAbb($value->pay_code_code): '');
            $attendances_by_employee[
                $value->emp_id . '___' . $value->att_date
            ] =  $avalue;

            $summary_total[$value->emp_id . '__'.$avalue][]=1;

            if($value->is_working){
                 if($value->pay_code_fixed_code !=4){
                    $summary_total[$value->emp_id . '__TPRESENT'][]=1;
                    if($value->pay_code_fixed_code ==2){
                        $summary_total[$value->emp_id . '__TLATEIN'][]=1;
                    }
                 }
                    
                 
            }else{
                
                $summary_total[$value->emp_id . '__TLEAVE'][]=1;

            } 

       
        }


        $monthperiod = CarbonPeriod::create(
                Carbon::parse($startDate),
                Carbon::parse($endDate)
        );

        $working_days=0;

        foreach ($monthperiod as $date) {
            $day = Carbon::parse($date)->format('D');

            $short = strtoupper(substr($day, 0, 2));

            $is_weekend = in_array($short, ['SU', 'SA']);
            if(!isset($holidayDates[$date->format('Y-m-d')]) && ! $is_weekend){
                 $working_days+=1;
            }

        }




        /*
        |--------------------------------------------------------------------------
        | Final Data
        |--------------------------------------------------------------------------
        */

        $data['attendances_by_employee'] = $attendances_by_employee;

        $data['depertment_wise_employee'] =
            $depertment_wise_employee;

        $data['summary_total'] = $summary_total;

        $data['holy_days'] = $holidayDates;
        $data['working_days'] = $working_days;
        $data['leave_codes'] = $leave_codes;

        $data['total_colspan'] =$data['totalDays']+7+count($leave_codes);

        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        $config = ['format'=>'A3','orientation'=>'L','show_watermark'=>false];
        if($request->format == 'pdf'){
            
            $pdf = Pdf::loadView('monthly_attendance', $data,[],$config);
            return $pdf->stream('attendance'.$monthName.'-'.$year.'.pdf');
        }else{
            return Excel::download(new ExportFromHtml($data,'monthly_attentence_dynamic'), 'attendance'.$monthName.'-'.$year.'.csv');
        }


        
    }
}