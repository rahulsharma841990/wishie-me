<?php

namespace App\Http\Controllers\API;

use App\Birthday;
use App\Http\Requests\BirthdayRequest;
use App\Http\Controllers\Controller;
use App\LabelMapping;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BirthdayController extends Controller
{
    public function create(BirthdayRequest $request){
        $requestData = $request->except(['image','label','birthday']);
        $birthdayModel = new Birthday;
        if($request->has('image') && $request->image != null){
            $imageName = $this->uploadFile($request);
            $requestData['image'] = $imageName;
        }
        $explodedDate = explode('-',$request->birthday);
        if(isset($explodedDate[2])){
            $requestData['birthday'] = Carbon::parse($request->birthday)->format('Y-m-d');
        }else{
            $requestData['birthday'] = Carbon::createFromFormat('d-m',$request->birthday)->format('m-d');
        }
        $requestData['created_by'] = Auth::user()->id;
        $birthdayModel->fill($requestData);
        $birthdayModel->save();
        $this->saveBirthdayLabels($request,$birthdayModel);
        return response()->json(['errors'=>null,'message'=>'Birthday created successfully!']);
    }

    protected function saveBirthdayLabels($request, $birthdayModel){
        foreach($request->label as $key => $label){
            $labelMappingModel = new LabelMapping;
            $labelMappingModel->birthday_id = $birthdayModel->id;
            $labelMappingModel->label_id = $label;
            $labelMappingModel->user_id = Auth::user()->id;
            $labelMappingModel->save();
        }
        return true;
    }

    private function uploadFile($request){
        $image = $request->input('image');
        preg_match("/data:image\/(.*?);/",$image,$image_extension);
        $image = preg_replace('/data:image\/(.*?);base64,/','',$image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'image_' . time() . '.' . $image_extension[1];
        Storage::disk('birthday')->put($imageName,base64_decode($image));
        return $imageName;
    }

    /** @noinspection PhpUnreachableStatementInspection */
    public function getBirthdays(){
        $birthdays = [];
        $birthdayRecords = Birthday::with(['labels'])
            ->whereCreatedBy(Auth::user()->id)
            ->orderBy(DB::raw('DATE_FORMAT(birthday,\'%m-%d\')'))
            ->get();
        $birthdays['Recent'] = $this->getRecentBirthdays($birthdayRecords);
        $birthdayRecords = $birthdayRecords->filter(function($birthday) use ($birthdays){
            $recentIds = collect($birthdays['Recent'])->groupBy('id')->keys()->toArray();
            return !in_array($birthday->id,$recentIds);
        });

        $birthdays['birthdays'] = $birthdayRecords->toArray();
        return response()->json($birthdays);

        $birthdays['Today'] = Birthday::with(['labels'])
            ->where(DB::raw('DATE_FORMAT(birthday,\'%m-%d\')'),'=',Carbon::today()->format('m-d'))
            ->whereCreatedBy(Auth::user()->id)
            ->get()->toArray();

        $recentIds = collect($birthdays['Recent'])->groupBy('id')->keys()->toArray();
        $todayIds = collect($birthdays['Today'])->groupby('id')->keys()->toArray();
        $tomorrowBirthdays = $this->getTomorrowBirthdays($birthdayRecords,$todayIds);
        $birthdays['Tomorrow'] = $tomorrowBirthdays->values()->toArray();
        $tomorrowIds = $tomorrowBirthdays->groupBy('id')->keys()->toArray();
        $birthdays['This Week'] = $this->getThisWeekBirthdays($birthdayRecords,$tomorrowIds,$todayIds,$recentIds);
        $birthdays['Next Week'] = $this->getNextWeekBirthdays($birthdayRecords,$tomorrowIds);
        $birthdays['Later This Month'] = $this->getLaterThisMonthBirthdays($birthdayRecords);
        $birthdayRecords = $this->getUpcomingBirthdays($birthdayRecords,$tomorrowIds,$todayIds,$recentIds);
        $lowYear = $birthdayRecords->filter(function($birthday){
            return (Carbon::parse($birthday->birthday)->format('Y') == Carbon::today()->format('Y'));
        });
        $highYear = $birthdayRecords->filter(function($birthday){
            return (Carbon::parse($birthday->birthday)->format('Y') > Carbon::today()->format('Y'));
        });
        $highYear = $this->sortBirthdays($highYear->toArray());
        $lowYear = $this->sortBirthdays($lowYear->toArray());
        $birthdaysArray = [];
        $index = 0;
        foreach($lowYear->groupBy('birthday') as $date => $birthdayList){
            $birthdaysArray[$index][$date] = $birthdayList->toArray();
            $index++;
        }
        foreach($highYear->groupBy('birthday') as $date => $birthdayList){
            $birthdaysArray[$index][$date] = $birthdayList->toArray();
            $index++;
        }
        $birthdays['upcomming'] = array_values($birthdaysArray);
        $birthdays = collect($birthdays)->filter(function($birthday){
            return !empty($birthday);
        });
        return response()->json($birthdays);
    }

    protected function sortBirthdays($toSort){
        usort($toSort,function($a,$b){
            $date1 = explode('-',$a['birth_date']);
            $date2 = explode('-',$b['birth_date']);
            if(isset($date1[2]) && isset($date2[2])){
                if(Carbon::parse($a['birth_date'])->format('m-d') == Carbon::parse($b['birth_date'])->format('m-d')){return 0;}
                return (Carbon::parse($a['birth_date'])->format('m-d') < Carbon::parse($b['birth_date'])->format('m-d'))? -1:1;
            }elseif(isset($date1[2]) && !isset($date2[2])){
                if(Carbon::parse($a['birth_date'])->format('m-d') == Carbon::createFromFormat('m-d',$b['birth_date'])->format('m-d')){return 0;}
                return (Carbon::parse($a['birth_date'])->format('m-d') < Carbon::createFromFormat('m-d',$b['birth_date'])->format('m-d'))? -1:1;
            }elseif(!isset($date1[2]) && isset($date2[2])){
                if(Carbon::createFromFormat('m-d',$a['birth_date'])->format('m-d') == Carbon::parse($b['birth_date'])->format('m-d')){return 0;}
                return (Carbon::createFromFormat('m-d',$a['birth_date'])->format('m-d') < Carbon::parse($b['birth_date'])->format('m-d'))? -1:1;
            }elseif(!isset($date1[2]) && !isset($date2[2])){
                if(Carbon::createFromFormat('m-d',$a['birth_date'])->format('m-d') == Carbon::createFromFormat('m-d',$b['birth_date'])->format('m-d')){return 0;}
                return (Carbon::createFromFormat('m-d',$a['birth_date'])->format('m-d') < Carbon::createFromFormat('m-d',$b['birth_date'])->format('m-d'))? -1:1;
            }
        });
        return collect($toSort);
    }

    protected function getUpcomingBirthdays($birthdayRecords,$tomorrowIds,$todayIds,$recentIds){
        $lastDayOfMonth = Carbon::parse('last day of this month');
        return $birthdayRecords->filter(function($birthday) use ($tomorrowIds,$todayIds,$recentIds, $lastDayOfMonth){
            return (!in_array($birthday->id,$tomorrowIds) &&
                !in_array($birthday->id,$todayIds) &&
                !in_array($birthday->id,$recentIds) &&
                Carbon::parse($birthday->birthday) > $lastDayOfMonth
            );
        });
    }

    protected function getRecentBirthdays($birthdayRecords){
        return $birthdayRecords->filter(function($birthday){
            $birthday->birthday; // compulsory just for get complete birth date
            $birthDate = $birthday->birth_date;
            $explodedDate = explode('-',$birthDate);
            if(isset($explodedDate[2])){
                $birthDate = Carbon::parse($birthDate);
            }else{
                $birthDate = Carbon::createFromFormat('m-d',$birthDate);
            }
            return ($birthDate->format('m-d') >= Carbon::now()->subDay(2)->format('m-d') &&
                $birthDate->format('m-d') < Carbon::today()->format('m-d')
            );
        })->values()->toArray();
    }

    protected function getLaterThisMonthBirthdays($birthdayRecords){
        $laterThisMonth = Carbon::parse('next week sunday');
        $lastDayOfMonth = Carbon::now()->endOfMonth();
        return $birthdayRecords->filter(function($birthday) use ($laterThisMonth,$lastDayOfMonth){
            $birthday->birthday; // compulsory just for get complete birth date
            $birthDate = $birthday->birth_date;
            $explodedDate = explode('-',$birthDate);
            if(isset($explodedDate[2])){
                $birthDate = Carbon::parse($birthDate);
            }else{
                $birthDate = Carbon::createFromFormat('m-d',$birthDate);
            }
            return ($birthDate->format('m-d') > $laterThisMonth->format('m-d') &&
                $birthDate->format('m-d') <= $lastDayOfMonth->format('m-d')
            );
        })->values()->toArray();
    }

    protected function getNextWeekBirthdays($birthdayRecords,$tomorrowIds){
        $nextWeek = Carbon::parse('next week');
        $nextWeekSunday = Carbon::parse('next week sunday');
        return $birthdayRecords->filter(function($birthday) use ($nextWeek, $nextWeekSunday,$tomorrowIds){
            $birthday->birthday; // compulsory just for get complete birth date
            $birthDate = $birthday->birth_date;
            $explodedDate = explode('-',$birthDate);
            if(isset($explodedDate[2])){
                $birthDate = Carbon::parse($birthDate);
            }else{
                $birthDate = Carbon::createFromFormat('m-d',$birthDate);
            }
            return ($birthDate->format('m-d') >= $nextWeek->format('m-d') &&
                $birthDate->format('m-d') <= $nextWeekSunday->format('m-d') &&
                !in_array($birthday->id,$tomorrowIds)
            );
        })->values()->toArray();
    }

    protected function getThisWeekBirthdays($birthdayRecords, $tomorrowIds, $todayIds, $recentIds){ //Skip tomorrow ids
        $thisWeek = Carbon::parse('this week');
        $thisSunday = Carbon::parse('this sunday');
        if($thisWeek->format('m') < $thisSunday->format('m')){
            $thisWeek = Carbon::now()->firstOfMonth();
        }
        return $birthdayRecords->filter(function($birthday) use ($thisWeek,$thisSunday,$tomorrowIds,$todayIds, $recentIds){
            $birthday->birthday; // compulsory just for get complete birth date
            $birthDate = $birthday->birth_date;
            $explodedDate = explode('-',$birthDate);
            if(isset($explodedDate[2])){
                $birthDate = Carbon::parse($birthDate);
            }else{
                $birthDate = Carbon::createFromFormat('m-d',$birthDate);
            }
            return ($birthDate->format('m-d') > Carbon::today()->format('m-d') &&
                $birthDate->format('m-d') <= $thisSunday->format('m-d') &&
                !in_array($birthday->id,$tomorrowIds) &&
                !in_array($birthday->id,$todayIds) &&
                !in_array($birthday->id,$recentIds)
            );
        })->values()->toArray();
    }

    protected function getTomorrowBirthdays($birthdayRecords,$todayIds){
        return $birthdayRecords->filter(function($birthday) use ($todayIds){
            $birthday->birthday; // compulsory just for get complete birth date
            $birthdayObject = $birthday;
            $birthDate = $birthday->birth_date;
            $explodedDate = explode('-',$birthDate);
            if(isset($explodedDate[2])){
                $birthday = Carbon::parse($birthDate);
            }else{
                $birthday = Carbon::createFromFormat('m-d',$birthDate);
            }
            return (
                $birthday->format('m-d') == Carbon::tomorrow()->format('m-d')
            );
        });
    }

    public function edit(Request $request, $id){
        $requestData = $request->except(['image','label','birthday']);
        $birthdayModel = Birthday::where(['created_by'=>Auth::user()->id])->find($id);
        if($birthdayModel == null){
            return response()->json(['errors'=>['birthday'=>['Birthday not found with give details']],'message'=>'Birthday not found!'],422);
        }
        if($request->hasFile('image')){
            $imageName = $this->uploadFile($request);
            $requestData['image'] = $imageName;
        }
        if($request->has('birthday')){
            $explodedDate = explode('-',$request->birthday);
            if(isset($explodedDate[2])){
                $requestData['birthday'] = Carbon::parse($request->birthday)->format('Y-m-d');
            }else{
                $requestData['birthday'] = Carbon::createFromFormat('d-m',$request->birthday)->format('m-d');
            }
        }
        $birthdayModel->fill($requestData);
        $birthdayModel->save();
        if($request->has('label')){
            $this->handleBirthdayLabels($request,$id,$birthdayModel);
        }
        return response()->json(['errors'=>null,'message'=>'Birthday updated successfully!']);

    }

    protected function handleBirthdayLabels($request,$id,$birthday){
        LabelMapping::where(['birthday_id'=>$id])->delete();
        $this->saveBirthdayLabels($request,$birthday);
    }

    public function delete($id){
        Birthday::where(['created_by'=>Auth::user()->id,'id'=>$id])->delete();
        LabelMapping::where(['user_id'=>Auth::user()->id,'birthday_id'=>$id])->delete();
        return response()->json(['errors'=>null,'message'=>'Birthday deleted successfully!']);
    }
}
