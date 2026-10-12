<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:units,code'],
            'name' => ['required', 'string', 'max:50'],
        ]);
        $data['code'] = strtoupper($data['code']);
        $unit = Unit::create($data);
        AuditLog::record('SETTINGS', "Created unit of measurement: {$unit->code}");

        return back()->with('success', 'Unit of measurement added.');
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('units', 'code')->ignore($unit)],
            'name' => ['required', 'string', 'max:50'],
        ]);
        $data['code'] = strtoupper($data['code']);
        $unit->update($data);
        AuditLog::record('SETTINGS', "Updated unit of measurement: {$unit->code}");

        return back()->with('success', 'Unit of measurement updated.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->productVariants()->exists()) {
            return back()->withErrors(['unit' => "Cannot delete {$unit->code} because it is in use."]);
        }
        $code = $unit->code;
        $unit->delete();
        AuditLog::record('SETTINGS', "Deleted unit of measurement: {$code}");

        return back()->with('success', 'Unit of measurement deleted.');
    }
}
