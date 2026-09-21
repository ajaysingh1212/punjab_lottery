<?php

namespace App\Http\Controllers\Admin\Ticketing;

use App\Http\Controllers\Controller;
use App\Models\Ticketing\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketTypeController extends Controller
{
    public function index()
    {
        return view('admin.ticketing.ticket-types.index', ['types' => TicketType::forCurrentAdmin()->with('prizes')->latest()->paginate(20)]);
    }

    public function create() { return view('admin.ticketing.ticket-types.form', ['type' => new TicketType()]); }

    public function store(Request $request)
    {
        $type = TicketType::create($this->data($request) + ['admin_id' => auth()->id()]);
        $this->syncPrizes($type, $request->input('prizes', []));
        return redirect()->route('admin.ticket-types.index')->with('success', 'Ticket type created.');
    }

    public function edit(TicketType $ticketType)
    {
        $this->guard($ticketType);
        return view('admin.ticketing.ticket-types.form', ['type' => $ticketType->load('prizes')]);
    }

    public function update(Request $request, TicketType $ticketType)
    {
        $this->guard($ticketType);
        $ticketType->update($this->data($request, $ticketType));
        $this->syncPrizes($ticketType, $request->input('prizes', []));
        return redirect()->route('admin.ticket-types.index')->with('success', 'Ticket type updated.');
    }

    public function destroy(TicketType $ticketType)
    {
        $this->guard($ticketType);
        $ticketType->delete();
        return back()->with('success', 'Ticket type deactivated.');
    }

    private function data(Request $request, ?TicketType $type = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'frequency' => 'required|in:daily,weekly,monthly,festival',
            'ticket_price' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'festival_name' => 'required_if:frequency,festival|nullable|string|max:150',
            'festival_date' => 'required_if:frequency,festival|nullable|date',
            'is_active' => 'nullable|boolean',
            'prizes.1' => 'required|numeric|min:1',
            'prizes.*' => 'nullable|numeric|min:0',
        ]) + ['is_active' => $request->boolean('is_active')];

        if ($request->hasFile('image')) {
            if ($type?->image) {
                Storage::disk('public')->delete($type->image);
            }
            $data['image'] = $request->file('image')->store('ticket-types', 'public');
        }

        return $data;
    }

    private function syncPrizes(TicketType $type, array $prizes): void
    {
        foreach (range(1, 10) as $position) {
            $amount = $prizes[$position] ?? null;
            if ($position === 1 || (float) $amount > 0) {
                $type->prizes()->updateOrCreate(['position' => $position], ['amount' => $amount]);
            } else {
                $type->prizes()->where('position', $position)->delete();
            }
        }
    }

    private function guard(TicketType $type): void
    {
        abort_unless(auth()->user()?->isSuperAdmin() || (int) $type->admin_id === (int) auth()->id(), 403);
    }
}
