<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{WorkingHour, Module};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkingHourController extends Controller
{
    protected $module_id = 7;
    protected $module;

    public function __construct()
    {
        $this->module = Module::find($this->module_id);
    }

    public function index()
    {
        $this->authorize('view', $this->module);
        
        $title = 'Working Hours';
        $module = $this->module;
        $workingHours = WorkingHour::latest()->paginate(15);
        $addedDays = $workingHours->pluck('day_type')->toArray();

        return view('admin.working_hours.index', compact('workingHours', 'title', 'module', 'addedDays'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', $this->module);
        
        $request->validate([
            'day_type' => [
                'required',
                Rule::in(['monday','tuesday','wednesday','thursday','friday','saturday','sunday','daily'])
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'is_overnight' => 'boolean',
            'is_active' => 'boolean'
        ]);

        WorkingHour::updateOrCreate(
            ['day_type' => $request->day_type],
            $request->all()
        );

        return redirect()->route('admin.working-hours.index')
            ->with('success', 'Working hours updated successfully!');
    }

    public function update(Request $request, WorkingHour $workingHour)
    {
        $this->authorize('update', $this->module);
        
        $request->validate([
            'day_type' => [
                'required',
                Rule::in(['monday','tuesday','wednesday','thursday','friday','saturday','sunday','daily'])
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'is_overnight' => 'boolean',
            'is_active' => 'boolean'
        ]);


        $workingHour->update($request->all());

        return redirect()->route('admin.working-hours.index')
            ->with('success', 'Working hours updated successfully!');
    }

    public function destroy(WorkingHour $workingHour)
    {
        $this->authorize('delete', $this->module);
        $workingHour->delete();
        
        return redirect()->route('admin.working-hours.index')
            ->with('success', 'Working hours deleted successfully!');
    }
}
