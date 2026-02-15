<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Services\LicensingService;

class ActivationController extends Controller
{
    protected LicensingService $licensing;

    public function __construct(LicensingService $licensing)
    {
        $this->licensing = $licensing;
    }

    /**
     * Show activation page
     */
    public function index()
    {
        $machineId = $this->licensing->getMachineId();
        $details = $this->licensing->getLicenseDetails();

        return view('system.activate', compact('machineId', 'details'));
    }

    /**
     * Process activation
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
        ]);

        $key = $request->license_key;

        if ($this->licensing->verifyLicense($key)) {
            $this->licensing->saveLicense($key);
            return redirect()->route('dashboard')->with('success', 'تم تفعيل السيستم بنجاح! شكراً لثقتك.');
        }

        return back()->with('error', 'كود التفعيل غير صالح لهذا الجهاز أو منتهي الصلاحية.');
    }
}
