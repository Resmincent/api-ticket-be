<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketReplyStoreRequest;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Resources\TicketReplyResource;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketReply;
use Exception;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{

    public function index(Request $request)
    {

        try {
            $query = Ticket::query();

            $query->orderBy('created_at', 'desc');

            if ($request->search) {
                $query->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%');
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->priority) {
                $query->where('priority', $request->priority);
            }

            if (auth()->user()->role === 'user') {
                $query->where('user_id', auth()->user()->id);
            }

            $tickets = $query
                ->with(['user', 'replies.user'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'Tickets retrieved successfully',
                'data' => TicketResource::collection($tickets),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while retrieving tickets',
                'data' => null,
            ], 500);
        }
    }

    public function store(TicketStoreRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $ticket = new Ticket();
            $ticket->user_id = auth()->user()->id;
            $ticket->code = 'TK-' . rand(100, 999);
            $ticket->title = $data['title'];
            $ticket->description = $data['description'];
            $ticket->priority = $data['priority'];
            $ticket->save();

            DB::commit();

            return response()->json([
                'message' => 'Ticket created successfully',
                'data' => new TicketResource($ticket)
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeTicketReply(TicketReplyStoreRequest $request, $code)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $ticket = Ticket::where('code', $code)->firstOrFail();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Ticket not found',
                    'data' => null,
                ], 404);
            }


            if (auth()->user()->role === 'user' && $ticket->user_id !== auth()->user()->id) {
                return response()->json([
                    'message' => 'Unauthorized access to this ticket',
                    'data' => null,
                ], 403);
            }

            $ticketReply = new TicketReply();
            $ticketReply->ticket_id = $ticket->id;
            $ticketReply->user_id = auth()->user()->id;
            $ticketReply->content = $data['content'];

            $ticketReply->save();

            if (auth()->user()->role === 'admin') {
                $ticket->status = $data['status'];
                if ($data['status'] === 'resolved') {
                    $ticket->resolved_at = now();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Ticket reply created successfully',
                'data' => new TicketReplyResource($ticket),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to find ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show($code)
    {
        try {
            $ticket = Ticket::where('code', $code)->with(['user', 'replies.user'])->firstOrFail();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Ticket not found',
                    'data' => null,
                ], 404);
            }

            if (auth()->user()->role === 'user' && $ticket->user_id !== auth()->user()->id) {
                return response()->json([
                    'message' => 'Unauthorized access to this ticket',
                    'data' => null,
                ], 403);
            }
            return response()->json([
                'message' => 'Ticket retrieved successfully',
                'data' => new TicketResource($ticket),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while retrieving tickets',
                'data' => null,
            ], 500);
        }
    }
}
