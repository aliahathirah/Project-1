<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use File;
use Storage;

class ScheduleController extends Controller
{
    //
    public function index(Request $request)
    {
         if($request->keyword){
             //search by title   
            $user = auth()->user();
            $schedules = $user->schedules()
                ->where('title','LIKE','%'.$request->keyword.'%')
                ->orWhere('description','LIKE','%'.$request->keyword.'%')
                ->paginate(3);
         }else{
            // query all schedule from 'schedules' table to $schedules
            // select * from schedules - SQL Query
            //$schedules = Schedule::all();
            $user = auth()->user();
            $schedules = $user->schedules()->paginate(3);   
        }
        
        // return to view with $schedules
        // resources/views/schedules/index.blade.php
        return view('schedules.index', compact('schedules'));
    }

    public function create(Request $request)
    {
        // this is schedule create form
        // show create form
        // resources/views/schedules/create.blade.php
        return view('schedules.create');
    }

    public function store(Request $request)
    {
        // store all input to table 'schedules' using model Schedule
        $validated = $request->validate([
            'title' => 'required',
            'description' => 'required|max:20'
            ]); 

        $schedule = new Schedule();
        $schedule->title = $request->title;
        $schedule->description = $request->description;
        $schedule->attachment = $request->attachment;
        $schedule->user_id = auth()->user()->id;
        $schedule->save();
   
        if($request->hasFile('attachment')){
            // rename file
            $filename = $schedule->id.'-'.date("Y-m-d").'.'.$request->attachment->getClientOriginalExtension();

            //store attachment on storage
            Storage::disk('public')->put($filename, File::get($request->attachment));

            // update row
            $schedule->attachment = $filename;
            $schedule->save();
        }

        //return to index
        return redirect()->route('schedule:index')->with([
            'alert-type' => 'alert-primary',
            'alert' => 'Your schedule has been saved!'
        ]);
    }

    public function show(Schedule $schedule)
    {
        return view('schedules.show', compact('schedule'));
    }

    public function edit(Schedule $schedule)
    {
        return view('schedules.edit', compact('schedule'));
    }

    public function update(Schedule $schedule, Request $request)
    {
        // update $schedule using input from edit form
        $schedule->title = $request->title;
        $schedule->description = $request->description;
        $schedule->attachment = $request->attachment;
        $schedule->save();

        // redirect to schedule index
        return redirect()->route('schedule:index')->with([
            'alert-type' => 'alert-success',
            'alert' => 'Your schedule has been updated!'
        ]);
    }

    public function destroy(Schedule $schedule)
    {
        if($schedule->attachment){
            Storage::disk('public')->delete($schedule->attachment);
        }

        //delete $schedule from db
        $schedule->delete();

        // return to schedule index
        return redirect()->route('schedule:index')->with([
            'alert-type' => 'alert-danger',
            'alert' => 'Your schedule has been deleted!'
        ]);
    }

    public function forceDestroy(Schedule $schedule)
    {
        $schedule->forceDelete();

        return redirect()->route('schedule:index')->with([
            'alert-type' => 'alert-danger',
            'alert' => 'Your schedule has been force deleted!'
        ]);
    }
}