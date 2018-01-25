<?php
/**
 * Created by PhpStorm.
 * User: Anna
 * Date: 10.12.2017
 * Time: 22:01
 */

namespace App\Http\Controllers;
use App\Event;
use JWTAuth;
use Illuminate\Http\Request;
class EventController extends Controller
{
    public function postEvent(Request $request)
    {
        $user = JWTAuth::parseToken()->toUser();
        $event = new Event();
        $event->type        = $request->input('type');
        $event->amount      = $request->input('amount');
        $event->category    = $request->input('category');
        $event->date        = date("d.m.Y h:i:s");
        $event->description = $request->input('description');
        $event->user_id     = $user->id;
        $event->save();
        return response()->json(['event' => $event], 201);
    }

    public function getEvents()
    {
        $user = JWTAuth::parseToken()->toUser();
        $events = Event::where('user_id', $user->id)->get();
        return response()->json($events, 200);
    }

    public function getEventById($id)
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        } else {
            return response()->json( $event, 200);
        }
    }

//    public function putQuote(Request $request, $id)
//    {
//        $quote = Quote::find($id);
//        if (!$quote) {
//            return response()->json(['message' => 'Document not found'], 404);
//        }
//        $quote->content = $request->input('content');
//        $quote->save();
//        return response()->json(['quote' => $quote], 200);
//    }
//    public function deleteQuote($id)
//    {
//        $quote = Quote::find($id);
//        $quote->delete();
//        return response()->json(['message' => 'Quote deleted'], 200);
//    }
}