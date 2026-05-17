<?php

namespace App\Services\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;



class ExportFromHtml implements FromView
{
    protected $data;
    protected $template;

    public function __construct($data,$template)
    {
        $this->data = $data;
        $this->template =$template;
    }

    public function view(): View
    {
        return view($this->template, $this->data);
    }


}