<?php

class MycalendarController extends BaseController
{
    public function getIndex()
    {
		return View::make('mycalendar.index');
    }
    
    public function getEvents()
    {
    	$start	= Input::get('start');
    	$end	= Input::get('end');
    	
    	$res = MyCalendarEvent::getEventsByStartAndEnd(Auth::user()->userId, $start, $end)->get();
    	
    	if(count($res)) {
    		return Response::json($res->toArray(), 200);
    	} else {
    		return Response::json(array(), 200);
    	}
    }
    
    public function getAddEvent($milliseconds)
    {
    	$timestamp	= $milliseconds/1000;
    	$date		= date('Y-m-d', $timestamp);
    	
    	$facebookAuth = array();
    	$twitterAuth  = array();
    	
    	$res = Sns::getSNSAuth(Auth::user()->userId)->get();
    	
    	if(count($res)) {
    		$facebookAuth	= json_decode($res[0]->facebook);
    		$twitterAuth	= json_decode($res[0]->twitter);
    	}
    	
    	return View::make(
    		'mycalendar.addEvent',
    		[
    			'facebookAuth'	=> $facebookAuth,
    			'twitterAuth'	=> $twitterAuth,
    			'milliseconds'	=> $milliseconds
    		]
    	);
    }
    
    public function postAddEvent($milliseconds)
    {
    	$timestamp	= $milliseconds/1000;
    	$date		= date('Y-m-d', $timestamp);
    	
    	// XXX: IMPORTANT - get all post data in one variable to reduce the call for Input::get
    	$postData = Input::all();
    	
    	// validate the info, create rules for the inputs
    	$rules = array(
    		'title'			=> 'required|max:255',
    		'start'			=> 'required|date',
    		'end'			=> 'date',
    		'memo'			=> 'max:2048',
    		'notifyEmail'	=> 'email',
    	);
    	
    	// run the validation rules on the inputs from the form
    	$validator = Validator::make($postData, $rules);
    	
    	// if the validator fails, redirect back to the form
    	if ($validator->fails()) {
    		// send back the input so that we can repopulate the form
    		return Redirect::to('/mycalendar/add/event/'.$milliseconds)->withErrors($validator)->withInput();
    	} else {
    		MyCalendarEvent::saveEvent(Auth::user()->userId, $postData);
    	
    		return Redirect::to('/mycalendar')->with('success', 'Calendar event has been updated successfully ');
    	}
    }
}
