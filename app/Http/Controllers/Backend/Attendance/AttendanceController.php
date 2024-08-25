<?php

namespace App\Http\Controllers\Backend\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\Tools\ToolsController;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Attendance;
use Auth;

class AttendanceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     * More info DataTables : https://yajrabox.com/docs/laravel-datatables/master
     * 
     * @param \Yajra\Datatables\Datatables $datatables
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function index(Datatables $datatables, Request $request)
    {
        $columns = [
            'id' => ['title' => 'No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'name' => ['name' => 'user.name'],
            'date' => ['title' => 'In Date'],
            'in_time',
            'date_out' => ['title' => 'Out Date'],
            'out_time',
            'work_hour',
            'over_time',
            'late_time',
            'early_out_time',
            'in_location_id' => ['name' => 'areaIn.name', 'title' => 'In Location'],
            'out_location_id' => ['name' => 'areaOut.name', 'title' => 'Out Location']
        ];

        $from = date($request->dateFrom);
        $to = date($request->dateTo);

        if ($datatables->getRequest()->ajax()) {
            $query = Attendance::with('user', 'user.shifts', 'areaIn', 'areaOut')
                ->select('attendances.*');

            if ($from && $to) {
                $query = $query->whereBetween('date', [$from, $to]);
            }

            // worker
            if (Auth::user()->hasRole('staff') || Auth::user()->hasRole('admin')) {
                $query = $query->where('worker_id', Auth::user()->id);
            }

            return $datatables->of($query)
                
                ->addColumn('name', function (Attendance $data) {
                    $color = $data->user->shifts[0]->color;
                    return '<span style="color: '. $color .'" class="badge badge-secondary">' . $data->user->name . '</span>';
                })
                ->addColumn('late_time', function (Attendance $data) {
                    return $data->late_time > '00:00:00' ? '<span style="color: red"><b>'. $data->late_time . '</b></span>' : $data->late_time;
                })
                ->addColumn('over_time', function (Attendance $data) {
                    return $data->over_time > '00:00:00' ? '<span style="color: green"><b>'. $data->over_time . '</b></span>' : $data->over_time;
                })
                ->addColumn('early_out_time', function (Attendance $data) {
                    return $data->early_out_time > '00:00:00' ? '<span style="color: red"><b>'. $data->early_out_time . '</b></span>' : $data->early_out_time;
                })
                ->addColumn('in_location_id', function (Attendance $data) {
                    return $data->in_location_id == null ? '' : $data->areaIn->name;
                })
                ->addColumn('out_location_id', function (Attendance $data) {
                    return $data->out_location_id == null ? '' : $data->areaOut->name;
                })
                ->rawColumns(['out_time', 'in_time', 'early_out_time', 'over_time', 'late_time', 'name', 'out_location_id', 'in_location_id'])
                ->toJson();
        }

        $toolsController = new ToolsController();
        $columnsArrExPr = $toolsController->ExportColumnArr(0, 12);
        
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[2,'desc'], [3,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $toolsController->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.attendances.index', compact('html'));
    }

    /**
     * Get script for the date range.
     *
     * @return string
     */
    public function scriptMinifiedJs()
    {
        // Script to minified the ajax
        return <<<CDATA
            var formData = $("#date_filter").find("input").serializeArray();
            $.each(formData, function(i, obj){
                data[obj.name] = obj.value;
            });
CDATA;
    }
}
