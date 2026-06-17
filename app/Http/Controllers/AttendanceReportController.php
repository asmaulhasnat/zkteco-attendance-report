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

    public function index(Request $request)
    {
        $data = [];
        $data['departments'] = $this->getDepartmentList($request);
        $data['employees'] = $this->getEmployeeList($request);;
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

        $request->validate([
            'format' => 'required',
            'month' => 'required_if:report_type,attendance',
            'start_date' => 'required_if:report_type,leave',
            'end_date' => 'required_if:report_type,leave',
        ]);

        $data = [];
        $data['departments'] = $this->getDepartmentList($request);
        $data['weekend'] =$this->weekendDayList();
        $data['employees'] =$employees = $this->getEmployeeList($request);
        $data['leave_codes']=$leave_codes  =$this->getLeaveCodes();
        $monthName ='';

        if($request->report_type=='attendance'){
            $month = $request->month;
            $date = Carbon::createFromFormat('Y-m', $month);
            $data['monthNumber'] = $monthNumber = $date->month;
            $data['monthName'] = $monthName = $date->format('F');
            $data['shortMonth'] = $shortMonth = $date->format('M');
            $data['year'] = $year = $date->year;

            $data['startDate'] = $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
            $data['endDate'] = $endDate = $date->copy()->endOfMonth()->format('Y-m-d');

            $data['totalDays'] = $date->daysInMonth;

        }else{
            $data['startDate'] = $startDate = $request->start_date;
            $data['endDate'] = $endDate = $request->end_date;
             $data['year']=$year = Carbon::createFromFormat('Y-m-d', $startDate)->format('Y');

        }
        
        $data['holy_days']=$holidayDates = $this->getHolidayList($startDate,$endDate);

        if($request->report_type=='attendance'){
            $depertment_wise_employee = [];

            foreach ($employees as $value) {
                $depertment_wise_employee[$value->department_id][] = $value;
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

            if ($request->employee) {
                $attendances = $attendances->where('aplc.emp_id', $request->employee);
            }

            $attendances = $attendances->groupBy('aplc.emp_id', 'aplc.att_date','apc.is_work')->get();

            $attendances_by_employee = [];
            $summary_total = [];

            foreach ($attendances as $value) {
                $day = Carbon::parse($value->att_date)->format('D');
                $short = strtolower($day);
                $is_weekend = in_array($short, $this->weekendDayList());

                if( $is_weekend &&  !$value->is_working){
                    continue;
                }

                if (in_array($value->att_date,$holidayDates)){
                    continue;
                }
                $avalue = trim($value->pay_code_symbol ?? '') !== ''? $this->generateAbb($value->pay_code_symbol): (trim($value->pay_code_code ?? '') !== ''? $this->generateAbb($value->pay_code_code): '');
                $attendances_by_employee[
                    $value->emp_id . '__' . $value->att_date
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

            $working_days= 0;

            foreach ($monthperiod as $date) {
                $day = Carbon::parse($date)->format('D');
                $short = strtolower($day);
                $is_weekend = in_array($short, $data['weekend']);
                if(!isset($holidayDates[$date->format('Y-m-d')]) && ! $is_weekend){
                    $working_days+=1;
                }

            }

            $data['attendances_by_employee'] = $attendances_by_employee;
            $data['depertment_wise_employee'] =$depertment_wise_employee;
            $data['summary_total'] = $summary_total;
            $data['working_days'] = $working_days;
            $data['total_colspan'] =$data['totalDays']+7+count($leave_codes);

            $config = ['format'=>'A3','orientation'=>'L','show_watermark'=>false];
            if($request->format == 'pdf'){
                
                $pdf = Pdf::loadView('monthly_attendance', $data,[],$config);
                return $pdf->stream('attendance'.$monthName.'-'.$year.'.pdf');
            }else{
                return Excel::download(new ExportFromHtml($data,'monthly_attentence_dynamic'), 'attendance'.$monthName.'-'.$year.'.csv');
            }
        }else{


            $employees_leave_balance = [];

            foreach($data['employees'] as $key=>$value){

                foreach(config('leave') as $lkey=>$lvalue){
                    $employees_leave_balance[$value->id.'___'.$lkey]=$lvalue;
                }

            }


            $leaves_previous_records = DB::table('att_leave as al')
                ->select(
                    'wwi.employee_id',
                    'al.pay_code_id',
                    'pc.code',
                    'pc.symbol',
                    DB::raw('SUM(al.leave_day) as total_leave_day')
                )
                ->join(
                    'workflow_workflowinstance as wwi',
                    'al.workflowinstance_ptr_id',
                    '=',
                    'wwi.id'
                )
                ->join('att_paycode as pc', 'pc.id', '=', 'al.pay_code_id')
                ->where('al.start_time', '<', $endDate)
                ->where('al.end_time', '>=', $data['year'].'-01-01');

            if ($request->employee) {
                $leaves_previous_records->where('wwi.employee_id', $request->employee);
            }

            $leaves_previous_records = $leaves_previous_records
                ->groupBy(
                    'wwi.employee_id',
                    'al.pay_code_id',
                    'pc.code',
                    'pc.symbol'
                )
                ->get();

            foreach($leaves_previous_records as $key=>$value){
                $leave_code = trim($value->symbol ?? '') !== ''
                    ? $this->generateAbb($value->symbol)
                    : (
                        trim($value->code ?? '') !== ''
                            ? $this->generateAbb($value->code)
                            : ''
                    );
                    $employees_leave_balance[$value->employee_id.'___'.$leave_code]=($employees_leave_balance[$value->employee_id.'___'.$leave_code] ?? 0)-($value->total_leave_day ?? 0);

            }

             


            $leaves_records = DB::table('att_leave as al')
            ->select(
                'al.apply_reason',
                'al.start_time',
                'al.end_time',
                'al.apply_time',
                'al.leave_day',
                'al.pay_code_id',
                'wwi.approval_time',
                'wwi.approval_remark',
                'wwi.approval_status',
                'wwi.employee_id',
                'pc.code',
                'pc.symbol'
            )
            ->join(
                'workflow_workflowinstance as wwi',
                'al.workflowinstance_ptr_id',
                '=',
                'wwi.id'
            )
            ->join('att_paycode as pc', 'pc.id', '=', 'al.pay_code_id')
            ->where('al.start_time', '<=', $endDate)
            ->where('al.end_time', '>=', $startDate);

            if ($request->employee) {
                $leaves_records->where('wwi.employee_id', $request->employee);
            }

            $leaves_records = $leaves_records->get();

            $leaves_record_by_employee = [];

            foreach ($leaves_records as $value) {

                $leave_code = trim($value->symbol ?? '') !== ''
                    ? $this->generateAbb($value->symbol)
                    : (
                        trim($value->code ?? '') !== ''
                            ? $this->generateAbb($value->code)
                            : ''
                    );

                $employees_leave_balance[$value->employee_id.'___'.$leave_code]=($employees_leave_balance[$value->employee_id.'___'.$leave_code] ?? 0)-($value->leave_day ?? 0);
                $leaves_record_by_employee[$value->employee_id][] = [
                    'leave_info' => $value,
                    'leave_code' => $leave_code,
                    'leave_balance' => $employees_leave_balance[$value->employee_id.'___'.$leave_code]
                ];

            }

            $data['leaves_record_by_employee'] = $leaves_record_by_employee;
            $data['employees_leave_balance'] = $employees_leave_balance;

            $config = ['format'=>'A4','orientation'=>'P','show_watermark'=>false];
            if($request->format == 'pdf'){
                
                $pdf = Pdf::loadView('leave_summary', $data,[],$config);
                return $pdf->stream('leave-'.$year.'.pdf');
            }else{
                return Excel::download(new ExportFromHtml($data,'leave_summary_dynamic'), 'leave-'.$year.'.csv');
            }

        }


        
    }


    public function getEmployeeList($request){
        return DB::table('personnel_employee as pe')
            ->selectRaw('pe.*,d.dept_name')
            ->join('personnel_position as pp', 'pp.id', '=', 'pe.position_id')
            ->join('personnel_department as d', 'd.id', '=', 'pe.department_id')
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
            ->when($request->employee, function ($query) use ($request) {
                $query->where('pe.id', $request->employee);
            })
            ->when($request->department, function ($query) use ($request) {
                $query->where('pe.department_id', $request->department);
            })
            ->get();;
    }

    public function getDepartmentList($request){
        return DB::table('personnel_department')
            ->when($request->department, function ($query) use ($request) {
                $query->where('id', $request->department);
            })
            ->get();
    }
    public function getHolidayList($startDate,$endDate){

        $holy_days = DB::table('att_holiday')
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->get();

        $holidayDates = [];

        foreach ($holy_days as $holiday) {
            $abbr ='';
            if (!empty($holiday->alias)) {
                $abbr =$this->generateAbb($holiday->alias);
            }

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

        return $holidayDates;

    }

     public function getLeaveCodes(){

        $leave_codes  =[];

        $leave_code_infos = DB::table('att_paycode')->where('is_work',0)->get();

        foreach($leave_code_infos as $key=>$value){
            $abbr = trim($value->symbol ?? '') !== ''? $this->generateAbb($value->symbol): (trim($value->code ?? '') !== ''? $this->generateAbb($value->code): '');
            $leave_codes[]=$abbr;
        }
        return $leave_codes;

    }

    public function weekendDayList(){
        return ["sat","sun"];
    }
}