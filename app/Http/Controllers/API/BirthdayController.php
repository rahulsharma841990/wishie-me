<?php

namespace App\Http\Controllers\API;

use App\Birthday;
use App\Http\Requests\BirthdayRequest;
use App\Http\Controllers\Controller;
use App\LabelMapping;
use Carbon\Carbon;
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
        $requestData['birthday'] = Carbon::parse($request->birthday)->format('Y-m-d');
        $requestData['created_by'] = Auth::user()->id;
        $birthdayModel->fill($requestData);
        $birthdayModel->save();
        $this->saveBirthdayLabels($request,$birthdayModel);
        $birthdays = Birthday::with(['labels'])->whereCreatedBy(Auth::user()->id)->get()->toArray();
        return response()->json(['errors'=>null,'message'=>'Birthday created successfully!','birthdays'=>$birthdays]);
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

    public function getBirthdays(){
        $birthdays = [];
        $birthdays['Recent'] = Birthday::with(['labels'])
            ->where(DB::raw('DATE_FORMAT(birthday,\'%m\')'),'>=',DB::raw('DATE_FORMAT((CURDATE() - INTERVAL 1 MONTH),\'%m\')'))
            ->where(DB::raw('DATE_FORMAT(birthday,\'%m\')'),'<',DB::raw('DATE_FORMAT(CURDATE(),\'%m\')'))
            ->whereCreatedBy(Auth::user()->id)
            ->get()->toArray();
        $birthdays['Today'] = Birthday::with(['labels'])
            ->where(DB::raw('DATE_FORMAT(birthday,\'%m-%d\')'),'=',Carbon::today()->format('m-d'))
            ->whereCreatedBy(Auth::user()->id)
            ->get()->toArray();
        $birthdayRecords = Birthday::with(['labels'])
            ->where(DB::raw('DATE_FORMAT(birthday,\'%m-%d\')'),'>',Carbon::today()->format('m-d'))
            ->whereCreatedBy(Auth::user()->id)
            ->orderBy(DB::raw('DATE_FORMAT(birthday,\'%m-%d\')'))
            ->get();
        $tomorrowBirthdays = $this->getTomorrowBirthdays($birthdayRecords);
        $birthdays['Tomorrow'] = $tomorrowBirthdays->toArray();
        $tomorrowIds = $tomorrowBirthdays->groupBy('id')->keys()->toArray();
        $birthdays['This Week'] = $this->getThisWeekBirthdays($birthdayRecords,$tomorrowIds);
        $birthdays['Next Week'] = $this->getNextWeekBirthdays($birthdayRecords);
        $birthdays['Later This Month'] = $this->getLaterThisMonthBirthdays($birthdayRecords);
        $afterThisMonthBirthdays = $this->getAfterThisMonthBirthdays($birthdayRecords);
        $birthdays = array_merge($birthdays,$afterThisMonthBirthdays->toArray());
        return response()->json(['errors'=>null,'birthdays'=>$birthdays]);
    }

    protected function getAfterThisMonthBirthdays($birthdayRecords){
        $lastDayOfMonth = Carbon::now()->endOfMonth();
        $birthdayRecords = $birthdayRecords->filter(function($birthday) use ($lastDayOfMonth){
            $birthday->birthday; // compulsory just for get complete birth date
            $birthDate = $birthday->birth_date;
            $birthDate = Carbon::parse($birthDate);
            return ($birthDate->format('m-d') > $lastDayOfMonth->format('m-d'));
        });
        return $birthdayRecords->groupBy('birthday');
    }

    protected function getLaterThisMonthBirthdays($birthdayRecords){
        $laterThisMonth = Carbon::parse('next week sunday');
        $lastDayOfMonth = Carbon::now()->endOfMonth();
        return $birthdayRecords->filter(function($birthday) use ($laterThisMonth,$lastDayOfMonth){
            $birthday->birthday; // compulsory just for get complete birth date
            $birthDate = $birthday->birth_date;
            $birthDate = Carbon::parse($birthDate);
            return ($birthDate->format('m-d') > $laterThisMonth->format('m-d') &&
                $birthDate->format('m-d') <= $lastDayOfMonth->format('m-d')
            );
        })->values()->toArray();
    }

    protected function getNextWeekBirthdays($birthdayRecords){
        $nextWeek = Carbon::parse('next week');
        $nextWeekSunday = Carbon::parse('next week sunday');
        return $birthdayRecords->filter(function($birthday) use ($nextWeek, $nextWeekSunday){
            $birthday->birthday; // compulsory just for get complete birth date
            $birthDate = $birthday->birth_date;
            $birthDate = Carbon::parse($birthDate);
            return ($birthDate->format('m-d') >= $nextWeek->format('m-d') &&
                $birthDate->format('m-d') <= $nextWeekSunday->format('m-d')
            );
        })->values()->toArray();
    }

    protected function getThisWeekBirthdays($birthdayRecords, $tomorrowIds){ //Skip tomorrow ids
        $thisWeek = Carbon::parse('this week');
        $thisSunday = Carbon::parse('this sunday');
        if($thisWeek->format('m') < $thisSunday->format('m')){
            $thisWeek = Carbon::now()->firstOfMonth();
        }
        return $birthdayRecords->filter(function($birthday) use ($thisWeek,$thisSunday,$tomorrowIds){
            $birthday->birthday; // compulsory just for get complete birth date
            $birthDate = $birthday->birth_date;
            $birthDate = Carbon::parse($birthDate);
            return ($birthDate->format('m-d') >= $thisWeek->format('m-d') &&
                $birthDate->format('m-d') <= $thisSunday->format('m-d') &&
                !in_array($birthday->id,$tomorrowIds)
            );
        })->values()->toArray();
    }

    protected function getTomorrowBirthdays($birthdayRecords){
        return $birthdayRecords->filter(function($birthday){
            $birthday->birthday; // compulsory just for get complete birth date
            $birthDate = $birthday->birth_date;
            $birthday = Carbon::parse($birthDate);
            return ($birthday->format('m-d') == Carbon::tomorrow()->format('m-d'));
        });
    }
}
