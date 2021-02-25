<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$setting['print_title']}}{{$form['template']['name']}}</title>
<link type="text/css" rel="stylesheet" href="{{mix('/assets/dist/app.min.css')}}" />
<style type="text/css">
.table {
    border: 1px solid #000;
    color: #000;
    width: 100%;
    margin: 5px 0;
}

.table .left { text-align:left; }
.table .center { text-align:center; }
.table .right { text-align:right; }

.table p { text-align:center; }
.table img { border:0; }
.table td {
    border: 1px solid #000;
    padding: 1px 2px;
    color: #000;
    vertical-align: middle;
    font-weight: normal;
    overflow: hidden;
    text-overflow: ellipsis;
}

.table th {
    font-weight: normal;
    color: #000;
    border: 1px solid #000;
    padding: 3px;
}

.table>tbody>tr>td, 
.table>tbody>tr>th, 
.table>tfoot>tr>td, 
.table>tfoot>tr>th, 
.table>thead>tr>td, 
.table>thead>tr>th {
    border-top: 1px solid #000;
}

.table.table-grid>tbody>tr>td, 
.table.table-grid>tbody>tr>th, 
.table.table-grid>tfoot>tr>td, 
.table.table-grid>tfoot>tr>th, 
.table.table-grid>thead>tr>td, 
.table.table-grid>thead>tr>th {
    display:table-cell; vertical-align:middle;
}

.table>thead>tr>th {
    border-bottom: 1px solid #000;
}

.title {
    text-align: center;
    font-size: 20px;
    font-weight: 600;
    color: #333;
    padding-bottom: 10px;
}

.main-container h3 {
    padding-top:12px;
    font-size:15px;
    text-align:center;
}

.main-container p {
    text-align:left;
    padding-top:6px;
}

.table.no-border,
.table.no-border td,
.table.no-border th {
    border: 0;
}

@media print {
    .panel {
        display: none; 
    }
    .main {
        margin: 0 auto;
        border: 0;
    }
    .main-container {
        font:11pt 'SimSun', 'STXihei', sans-serif;
    }
    label {
        font:11pt 'SimSun', 'STXihei', sans-serif;
    }
    .i-checks-sm>i {
        width: 14px;
        height: 14px;
        margin-right: 6px;
        margin-left: -18px;
    }
}

.text-muted {
    color: #000;
}

.b-t {
    border-top: 1px solid #000;
 }

.row {
    border: 1px solid #000;
    border-top: 0;
    padding: 5px;
    color: #000;
    margin-left: 0;
    margin-right: 0;
}

.row > div {
    padding: 2px 6px;
}

.row:first-child {
    border-top: 1px solid #000;
}

.row.no-border {
    border: 0;
    padding: 0;
}

@media print {
   .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12 {
        float: left;
   }
   .col-sm-12 {
        width: 100%;
   }
   .col-sm-11 {
        width: 91.66666667%;
   }
   .col-sm-10 {
        width: 83.33333333%;
   }
   .col-sm-9 {
        width: 75%;
   }
   .col-sm-8 {
        width: 66.66666667%;
   }
   .col-sm-7 {
        width: 58.33333333%;
   }
   .col-sm-6 {
        width: 50%;
   }
   .col-sm-5 {
        width: 41.66666667%;
   }
   .col-sm-4 {
        width: 33.33333333%;
   }
   .col-sm-3 {
        width: 25%;
   }
   .col-sm-2 {
        width: 16.66666667%;
   }
   .col-sm-1 {
        width: 8.33333333%;
   }
}

.i-checks>i {
    border: 1px solid #000;
}
.i-checks input:checked+i {
    border-color: #000;
}
.i-checks input:checked+i:before {
    background-color: #000;
}

@page {
    font:11pt 'SimSun', sans-serif;
    margin: 5mm 10mm 20mm 10mm;
    size: 210mm 297mm;
    prince-pdf-page-colorspace: auto;
    prince-pdf-page-label: auto;
    prince-rotate-body: 0deg;
    prince-shrink-to-fit: none;
    @bottom {
        content: "第" counter(page)"页，共"counter(pages)"页"
    }
}

@media print {
    .main-container {
        font:11pt 'SimSun', 'STXihei', sans-serif;
    }
    label {
        font:11pt 'SimSun', 'STXihei', sans-serif;
    }
}

.table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
    padding: 3px;
}

</style>
</head>

<body>
    <div class="main-container">
        <div class="title">{{$setting['print_title']}}{{$form['template']['name']}}</div>
        <div>
            {{$content}}
        </div>
    </div>
</body>
</html>
