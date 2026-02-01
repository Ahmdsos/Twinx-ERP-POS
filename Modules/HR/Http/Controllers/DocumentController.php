<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HR\Models\Employee;
use Modules\HR\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $request->validate([
            'document_type' => 'required|string|max:255',
            'document_number' => 'required|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $filePath = null;
        if ($request->hasFile('document_file')) {
            $filePath = $request->file('document_file')->store('hr/documents/' . $employee->id, 'public');
        }

        $employee->documents()->create([
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'file_path' => $filePath,
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'تم إضافة الوثيقة بنجاح.');
    }

    public function download(Document $document)
    {
        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'الملف غير موجود.');
        }

        return Storage::disk('public')->download($document->file_path);
    }

    public function destroy(Document $document)
    {
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();
        return back()->with('success', 'تم حذف الوثيقة بنجاح.');
    }
}
