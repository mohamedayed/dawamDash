<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Attendance;
use App\Models\Location;
use App\Models\Setting;
use App\Models\ShiftUser;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ApiAttendanceController extends Controller
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
     * Store data attendance to DB
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiSaveAttendance(Request $request)
    {
        // Get all request
        $new = $request->all();

        // Get data setting
        $getSetting = Setting::find(1);

        // Get data from request
        $key = $new['key'];

        // Get user position
        $lat = $new['lat'];
        $longt = $new['longt'];

        $areaId = $new['area_id'];
        $q = $new['q'];
        $workerId = $new['worker_id'];

        

        if (!empty($key)) {
            if ($key == $getSetting->key_app) {
                // Check the area is exist
                $getPoly = Location::whereIn('area_id', [$areaId])->get(['lat', 'longt']);
                if ($getPoly->count() == 0) {
                    $data = [
                        'message' => 'location not found',
                    ];
                    return response()->json($data);
                }

                // Check if user inside the area
                $isInside = $this->isInsidePolygon($lat, $longt, $getPoly);
                if (!$isInside) {
                    $data = [
                        'message' => 'cannot attend',
                    ];
                    return response()->json($data);
                }

                // Check-in
                if ($q == 'in') {
                    // Get date
                    $inDate = Carbon::now()->timezone($getSetting->timezone);

                    // Get data from request
                    $inTime = new Carbon(Carbon::now()->timezone($getSetting->timezone)->format('H:i:s'));

                    // Check if user already check-in
                    $checkAlreadyCheckIn = Attendance::where('worker_id', $workerId)
                        ->where('date', Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d'))
                        ->where('in_time', '<>', null)
                        ->where('late_time', '<>', null)
                        ->where('out_time', null)
                        ->where('out_location_id', null)
                        ->first();

                    if ($checkAlreadyCheckIn) {
                        $data = [
                            'message' => 'already check-in',
                        ];
                        return response()->json($data);
                    }

                    // Get late time
                    $lateTime = $this->getLateHour($inTime, $inDate, $workerId);

                    $location = Area::find($areaId)->name;

                    // Save the data
                    $save = new Attendance();
                    $save->worker_id = $workerId;
                    $save->date = $inDate->format('Y-m-d');
                    $save->in_location_id = $areaId;
                    $save->in_time = $inTime;
                    $save->late_time = $lateTime;

                    $createNew = $save->save();

                    // Saving
                    if ($createNew) {
                        return response()->json($this->returnDataJson($inDate->format('Y-m-d'), $inTime, $location, 'Check-in'));
                    }

                    $data = [
                        'message' => 'Error! Something Went Wrong!',
                    ];
                    return response()->json($data);
                }

                // Check-out
                if ($q == 'out') {
                    // Get data from request
                    $outTime = new Carbon(Carbon::now()->timezone($getSetting->timezone)->format('H:i:s'));
                    $outDate = Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d');

                    $getInTime = Attendance::where('worker_id', $workerId)
                        ->where('out_time', null)
                        ->where('date_out', null)
                        ->where('out_location_id', null)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    // return response()->json($getInTime);

                    if (!$getInTime) {
                        $data = [
                            'message' => 'check-in first',
                        ];
                        return response()->json($data);
                    }

                    // Get data total working hour
                    $getWorkHour = $this->getWorkingHours($getInTime->in_time, $getInTime->date, $outTime);

                    // Get over time
                    $getOverHour = $this->getOverHours($workerId, $getInTime->date, $getInTime->in_time, $outTime);

                    // Early out time
                    $earlyOutTime = $this->getEarlyOutHour($workerId, $getInTime->date, $getInTime->in_time, $outTime);

                    // Get location
                    $location = Area::find($areaId)->name;

                    // Update the data
                    $getInTime->out_time = $outTime;
                    $getInTime->over_time = $getOverHour;
                    $getInTime->work_hour = $getWorkHour;
                    $getInTime->early_out_time = $earlyOutTime;
                    $getInTime->date_out = $outDate;
                    $getInTime->out_location_id = $areaId;

                    $updateData = $getInTime->save();

                    // Updating
                    if ($updateData) {
                        return response()->json($this->returnDataJson($outDate, $outTime, $location, 'Check-Out'));
                    }
                    $data = [
                        'message' => 'Error! Something Went Wrong!',
                    ];
                    return response()->json($data);
                }
                $data = [
                    'message' => 'Error! Wrong Command!',
                ];
                return response()->json($data);
            }
            $data = [
                'message' => 'The KEY is Wrong!',
            ];
            return response()->json($data);
        }
        $data = [
            'message' => 'Please Setting KEY First!',
        ];
        return response()->json($data);
    }

     /**
      * Return data json for the app
      * @param mixed $date
      * @param mixed $time
      * @param mixed $location
      * @param mixed $query
      * @return array
      */

    public function returnDataJson($date, $time, $location, $query){
        $data = [
            'message' => 'Success!',
            'date' => Carbon::parse($date)->format('Y-m-d'),
            'time' => Carbon::parse($time)->format('H:i:s'),
            'location' => $location,
            'query' => $query,
        ];
        return $data;
    }

    /**
     * Check if user inside the area
     * 
     * @param mixed $x
     * @param mixed $y
     * @param mixed $polygon
     * @return bool
     */
    public function isInsidePolygon($x, $y, $polygon)
    {
        $inside = false;
        for ($i = 0, $j = count($polygon) - 1, $iMax = count($polygon); $i < $iMax; $j = $i++) {
            $xi = $polygon[$i]['lat'];
            $yi = $polygon[$i]['longt'];
            $xj = $polygon[$j]['lat'];
            $yj = $polygon[$j]['longt'];

            $intersect = (($yi > $y) != ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Get late hour
     * @param mixed $inTime
     * @param mixed $workerId
     * @return mixed
     */
    public function getLateHour($inTime, $inDate, $workerId){
        $getInTime = new Carbon($inDate->format('Y-m-d') . " " . $inTime->format('H:i:s'));
        $endtime = Carbon::createFromFormat('H:i:s', $this->getDataWorker($workerId)->shift->end_time)->format('H:i:s');

        if($this->isDayPlusOne($workerId, $getInTime) && $this->isDayPlusOne($workerId, $endtime)){
            $getData = new Carbon($inDate->yesterday()->format('Y-m-d'). " " . $this->getDataWorker($workerId)->shift->start_time);
        }else{
            $getData = new Carbon($inDate->format('Y-m-d') . " " . $this->getDataWorker($workerId)->shift->start_time);
        }
    
        // Get late time
        if (!$getInTime->gt($getData)) {
            $lateTime = "00:00:00";
        } else {
            $lateTime = $getInTime->diff($getData)->format('%H:%I:%S');
        }

        return $lateTime;
    }

    /**
     * Get data shift for worker
     * @param mixed $workerId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getDataWorker($workerId){
        return ShiftUser::with(['shift', 'user'])
            ->whereHas('user', function ($query) use ($workerId) {
                $query->where('id', $workerId);
            })
            ->first();
    }

    /**
     * Calculate sum start time and late mark time
     * @param mixed $workerId
     * @return string
     */
    public function sumStartLateTime($workerId){
        $getData = $this->getDataWorker($workerId);
        $getStartTime = $getData->shift->start_time;
        $getLateTime = $getData->shift->late_mark_after;
        $result = date("H:i:s", strtotime($getStartTime)+strtotime($getLateTime));

        return $result;
    }

    /**
     * Check if the shift include next day
     * @param mixed $workerId
     * @return bool
     */
    public function isDayPlusOne($workerId, $endtime){
        $getData = $this->getDataWorker($workerId);
        $time = Carbon::createFromFormat('H:i:s', "23:59:59")->format('H:i:s');
        $begintime = Carbon::createFromFormat('H:i:s', $getData->shift->start_time)->format('H:i:s');

        if ($begintime < $endtime) {
            return $begintime <= $time && $time <= $endtime;
        } else {
            return $time >= $begintime || $time <= $endtime;
        }
    }

    /**
     * Get total Working Hours
     * 
     * @param mixed $inTime
     * @param mixed $inDate
     * @param mixed $outTime
     * @return string
     */
    public function getWorkingHours($inTime, $inDate, $outTime){
        $dataOutTime = new Carbon($outTime);
        $dataInTime = new Carbon($inDate->format('Y-m-d') . " " . $inTime);
        return $dataOutTime->diffInHours($dataInTime) . ':' . $dataOutTime->diff($dataInTime)->format('%I:%S');
    }

    /**
     * Get total over hour
     * @param mixed $workerId
     * @param mixed $inDate
     * @param mixed $inTime
     * @param mixed $outDate
     * @param mixed $outTime
     * @return mixed
     */
    public function getOverHours($workerId, $inDate, $inTime, $outTime){
        $getInTime = new Carbon($inDate->format('Y-m-d') . " " .$inTime);
        $endtime = Carbon::createFromFormat('H:i:s', $this->getDataWorker($workerId)->shift->end_time)->format('H:i:s');

        if($this->isDayPlusOne($workerId, $endtime)){
            $getData = new Carbon($inDate->addDays(1)->format('Y-m-d'). " " . $this->getDataWorker($workerId)->shift->end_time);
        }else{
            $getData = new Carbon($inDate->format('Y-m-d') . " " . $this->getDataWorker($workerId)->shift->end_time);
        }
       
        // Get over time
        if ($getInTime->gt($getData) || !$outTime->gt($getData)) {
            $getOverHours = "00:00:00";
        } else {
            $getOverHours = $outTime->diff($getData)->format('%H:%I:%S');
        }

        return $getOverHours;
    }

    /**
     * Get total early out hour
     * 
     * @param mixed $workerId
     * @param mixed $inDate
     * @param mixed $inTime
     * @param mixed $outTime
     * @return string
     */
    public function getEarlyOutHour($workerId, $inDate, $inTime, $outTime){
        $getInTime = new Carbon($inDate->format('Y-m-d') . " " . $inTime);
        $endtime = Carbon::createFromFormat('H:i:s', $this->getDataWorker($workerId)->shift->end_time)->format('H:i:s');

        if($this->isDayPlusOne($workerId, $endtime)){
            $getData = new Carbon($inDate->addDays(1)->format('Y-m-d'). " " . $this->getDataWorker($workerId)->shift->end_time);
        }else{
            $getData = new Carbon($inDate->format('Y-m-d') . " " . $this->getDataWorker($workerId)->shift->end_time);
        }

        // Early out time
        if ($getInTime->gt($getData) || $outTime->gt($getData)) {
            $getEarlyOutHour = "00:00:00";
        } else {
            $getEarlyOutHour = $getData->diff($outTime)->format('%H:%I:%S');
        }

        return $getEarlyOutHour;
    }
}
