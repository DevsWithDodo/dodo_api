<?php

namespace App\Http\Controllers;

use App\Exports\GroupExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Http\Controllers\CurrencyController;
use App\Notifications\Groups\ChangedGroupNameNotification;
use App\Notifications\Groups\GroupBoostedNotification;
use App\Http\Resources\GroupResource as GroupResource;
use App\Group;
use Auth;
use DB;
use Illuminate\Support\Facades\App;

class GroupController extends Controller
{
    public function index()
    {
        $user = auth('api')->user();
        return response()->json(['data' => $user->groups->map(function ($i) {
            return ['group_id' => $i->id, 'group_name' => $i->name, 'currency' => $i->currency];
        })]);
    }

    public function show(Request $request, Group $group)
    {
        $this->authorize('member', $group);
        $user = $request->user();
        $user->update(['last_active_group' => $group->id]);
        return new GroupResource($group);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_name' => 'required|string|min:1|max:20',
            'currency' => ['required', 'string', 'size:3', Rule::in(CurrencyController::currencyList())],
            'admin_approval' => 'boolean',
            'member_nickname' => ['required', 'string', 'min:1', 'max:15'],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $group = DB::transaction(function () use ($request) {
            $group = Group::create([
                'name' => $request->group_name,
                'currency' => $request->currency,
                'invitation' => Str::random(20),
                'admin_approval' => $request->admin_approval ?? false
            ]);

            $group->members()->attach(auth('api')->user()->id, [
                'nickname' => encrypt($request->member_nickname),
                'balance' => encrypt("0"),
                'is_admin' => true //set to true on first member
            ]);
            return $group;
        });

        return response()->json(new GroupResource($group), 201);
    }

    public function isBoosted(Request $request, Group $group)
    {
        $this->authorize('member', $group);
        $user = $request->user();
        return response()->json(['data' => [
            'is_boosted' => $group->boosted ? 1 : 0,
            'available_boosts' => $user->available_boosts,
            'trial' => $user->trial ? 1 : 0,
            'created_at' => $group->created_at
        ]]);
    }
    public function boost(Request $request, Group $group)
    {
        $this->authorize('boost', $group);
        $user = $request->user();
        DB::transaction(function () use ($user, $group) {
            $user->decrement('available_boosts');
            $group->update(['boosted' => true]);
        });

        foreach ($group->members->except($user->id) as $member) {
            $member->sendNotification((new GroupBoostedNotification($group, $user)));
        }

        return response()->json(null, 204);
    }

    public function update(Request $request, Group $group)
    {
        $this->authorize('edit', $group);
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|min:1|max:20',
            'currency' => ['nullable', 'string', 'size:3', Rule::in(CurrencyController::currencyList())],
            'admin_approval' => 'nullable|boolean',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        if ($request->has('admin_approval')) {
            return abort(400, __('errors.admin_approval_deprecated'));
        }

        $user = auth('api')->user();
        $old_name = $group->name;
        $group->update($request->only('name', 'currency'));

        if ($old_name != $group->name)
            foreach ($group->members->except($user->id) as $member)
                $member->sendNotification((new ChangedGroupNameNotification($group, $user, $old_name, $group->name)));

        return response()->json(new GroupResource($group), 200);
    }

    public function delete(Group $group)
    {
        $this->authorize('edit', $group);
        $group->delete();
        return response()->json(null, 204);
    }

    public function exportXls(Group $group, Request $request)
    {
        if (!$request->hasValidSignature()) abort(401, "Unauthorized.");

        App::setLocale($request->language);
        return Excel::download(new GroupExport($group), $group->name . '.xlsx');
    }

    public function exportPdf(Group $group, Request $request)
    {
        if (!$request->hasValidSignature()) abort(401, "Unauthorized.");

        App::setLocale($request->language);
        $purchases = $group->purchases()
                        ->orderBy('purchases.updated_at', 'desc')
                        ->with('receivers')
                        ->get();
        $payments = $group->payments()
                        ->orderBy('payments.updated_at', 'desc')
                        ->with('payer')
                        ->get();
        $mpdf = new \Mpdf\Mpdf(['tempDir' => storage_path('tempdir')]);
        $mpdf->WriteHTML(view('pdf', ['purchases' => $purchases, 'payments' => $payments, 'group' => $group]));
        return $mpdf->Output($group->name, 'I');

    }

    public function getFromInvitation(string $invitation) {
        $user = Auth::user();
        $query = Group::query();
        if ($user !== null) {
            $query->with('guests:id,group_user.nickname');
        }
        $group = $query->firstWhere('invitation', $invitation);
        if ($group === null) {
            return abort(422, __('errors.invalid_invitation'));
        }
        return $group->only([
            'id',
            'name',
            'currency',
            'admin_approval',
            'guests'
        ]);
    }
}
