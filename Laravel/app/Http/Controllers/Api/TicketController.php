<?php

namespace App\Http\Controllers\Api;

use App\Models\Screening;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TicketController extends ApiController
{
    private const RELATIONS = [
        'screening:id,movie_id,hall_id,starts_at',
        'screening.movie:id,title',
        'screening.hall:id,name',
        'customer:id,first_name,last_name,email',
    ];

    public function index(Request $request): JsonResponse
    {
        return $this->listResponse($request, Ticket::with(self::RELATIONS), Ticket::FILTERS);
    }

    public function store(Request $request): JsonResponse
    {
        $ticket = Ticket::create($this->validateTicket($request));

        return response()->json($ticket->load(self::RELATIONS), 201);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        return response()->json($ticket->load(self::RELATIONS));
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $ticket->update($this->validateTicket($request, $ticket));

        return response()->json($ticket->load(self::RELATIONS));
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $ticket->delete();

        return response()->json(null, 204);
    }

    private function validateTicket(Request $request, ?Ticket $ticket = null): array
    {
        // For PATCH the seat may be checked against values already stored in the ticket
        $screeningId = $request->input('screening_id', $ticket?->screening_id);
        $seatRow = $request->input('seat_row', $ticket?->seat_row);

        $data = $this->validatePayload($request, [
            'screening_id' => ['required', 'integer', 'exists:screenings,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'seat_row' => ['required', 'integer', 'min:1'],
            'seat_number' => [
                'required', 'integer', 'min:1',
                Rule::unique('tickets')
                    ->where('screening_id', $screeningId)
                    ->where('seat_row', $seatRow)
                    ->ignore($ticket),
            ],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', Rule::in(Ticket::STATUSES)],
        ]);

        $hall = Screening::with('hall')->find($screeningId)?->hall;
        $errors = [];
        if ($hall !== null && ($data['seat_row'] ?? $ticket?->seat_row) > $hall->rows_count) {
            $errors['seat_row'] = "Hall \"{$hall->name}\" has only {$hall->rows_count} rows.";
        }
        if ($hall !== null && ($data['seat_number'] ?? $ticket?->seat_number) > $hall->seats_per_row) {
            $errors['seat_number'] = "Hall \"{$hall->name}\" has only {$hall->seats_per_row} seats per row.";
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }
}
