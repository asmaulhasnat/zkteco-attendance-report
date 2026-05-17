<!DOCTYPE html>
<html>
<head>


<title>Attendance Report</title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<style>
@page {
    margin: 100px 10px 50px 10px;
    header: page-header;
    footer: page-footer;
}

body {
    font-size: 11px;
    font-family: Arial, sans-serif;
}

/* ---------------- TABLE ---------------- */
.table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.table th,
.table td {
    border: 1px solid #000;
    padding: 2px;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
    overflow: hidden;
}

/* ---------------- HEADERS ---------------- */
.title {
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    padding: 5px;
}

/* ---------------- SECTION ---------------- */
.section {
    font-weight: bold;
    text-align: left;
    padding: 4px;
}

/* ---------------- WEEKEND ---------------- */
.weekend {
    background: #f3f4f6;
}

/* ---------------- PRESENT ---------------- */
.present {
    background: #dcfce7;
    font-weight: bold;
}

/* ---------------- LEFT ALIGN ---------------- */

/* ---------------- COLUMN WIDTH CONTROL ---------------- */
.name-col {
    width: 120px;
}

.pos-col {
    width: 120px;
}

.day-col {
    width: 18px;
}

/* ---------------- PAGE NUMBER ---------------- */
.page-number {
    font-size: 10px;
    text-align: right;
}
</style>
</head>

<body>

<htmlpageheader name="page-header">
    <br>
    <div class="title">
        Employee Attendance Report - {{ $monthName }} {{ $year }}
    </div>
</htmlpageheader>

<htmlpagefooter name="page-footer">
    <div class="page-number">
        Page {PAGENO} of {nbpg}
    </div>
    <br>
</htmlpagefooter>
@include('monthly_attentence_dynamic')
</body>
</html>