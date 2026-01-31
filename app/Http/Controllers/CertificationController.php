<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificationController extends Controller
{
    public const CERT_TYPES = [
        'permiso_circulacion' => 'Permiso de Circulación',
        'technical_review' => 'Revisión Técnica',
        'soap' => 'SOAP',
        'analisis_gases' => 'Análisis de Gases',
        'seguro_adicional' => 'Seguro Adicional',
        'certificado_grua' => 'Certificado Grúa',
        'certificado_carga' => 'Certificado de Carga',
        'certificado_transporte' => 'Certificado de Transporte',
        'otro' => 'Otro',
    ];

    public function create(int $vehicleId)
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        return view('certificaciones.create', [
            'vehicle' => $vehicle,
            'certTypes' => self::CERT_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'type' => ['required', 'string', Rule::in(array_keys(self::CERT_TYPES))],
            'name' => ['required', 'string', 'max:255'],
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'expiration_date' => ['required', 'date'],
            'provider' => ['nullable', 'string', 'max:255'],
            'cost' => ['nullable', 'integer', 'min:0'],
            'observations' => ['nullable', 'string'],
            'required' => ['boolean'],
            'attached_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'attached_file_2' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $validated['active'] = true;
        if ($request->hasFile('attached_file')) {
            $validated['attached_file'] = $request->file('attached_file')->store('certifications/' . $validated['vehicle_id'], 'public');
        } else {
            unset($validated['attached_file']);
        }
        if ($request->hasFile('attached_file_2')) {
            $validated['attached_file_2'] = $request->file('attached_file_2')->store('certifications/' . $validated['vehicle_id'], 'public');
        } else {
            unset($validated['attached_file_2']);
        }

        Certification::create($validated);
        return redirect()
            ->route('vehiculos.show', $validated['vehicle_id'])
            ->with('success', 'Certificación creada correctamente.');
    }

    public function edit(int $id)
    {
        $certification = Certification::with('vehicle')->findOrFail($id);
        return view('certificaciones.edit', [
            'certification' => $certification,
            'vehicle' => $certification->vehicle,
            'certTypes' => self::CERT_TYPES,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $certification = Certification::findOrFail($id);
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys(self::CERT_TYPES))],
            'name' => ['required', 'string', 'max:255'],
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'expiration_date' => ['required', 'date'],
            'provider' => ['nullable', 'string', 'max:255'],
            'cost' => ['nullable', 'integer', 'min:0'],
            'observations' => ['nullable', 'string'],
            'required' => ['boolean'],
            'active' => ['boolean'],
            'attached_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'attached_file_2' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        if ($request->hasFile('attached_file')) {
            if ($certification->attached_file) {
                Storage::disk('public')->delete($certification->attached_file);
            }
            $validated['attached_file'] = $request->file('attached_file')->store('certifications/' . $certification->vehicle_id, 'public');
        } else {
            // Preservar la ruta existente si no se sube archivo nuevo
            $validated['attached_file'] = $certification->attached_file;
        }
        if ($request->hasFile('attached_file_2')) {
            if ($certification->attached_file_2) {
                Storage::disk('public')->delete($certification->attached_file_2);
            }
            $validated['attached_file_2'] = $request->file('attached_file_2')->store('certifications/' . $certification->vehicle_id, 'public');
        } else {
            $validated['attached_file_2'] = $certification->attached_file_2;
        }

        $certification->update($validated);
        return redirect()
            ->route('vehiculos.show', $certification->vehicle_id)
            ->with('success', 'Certificación actualizada correctamente.');
    }

    public function destroy(int $id)
    {
        $certification = Certification::findOrFail($id);
        $vehicleId = $certification->vehicle_id;
        if ($certification->attached_file) {
            Storage::disk('public')->delete($certification->attached_file);
        }
        if ($certification->attached_file_2) {
            Storage::disk('public')->delete($certification->attached_file_2);
        }
        $certification->delete();
        return redirect()
            ->route('vehiculos.show', $vehicleId)
            ->with('success', 'Certificación eliminada correctamente.');
    }

    public function download(int $id, int $slot): StreamedResponse
    {
        $certification = Certification::findOrFail($id);
        $path = $slot === 1 ? $certification->attached_file : $certification->attached_file_2;
        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }
        $filename = $this->readableFilename($certification, $slot, $path);
        return Storage::disk('public')->download($path, $filename, ['Content-Type' => Storage::disk('public')->mimeType($path)]);
    }

    /**
     * Ver archivo en el navegador (inline) en lugar de descargar.
     * Evita depender del symlink public/storage que puede dar 404 en Docker.
     */
    public function view(int $id, int $slot)
    {
        $certification = Certification::findOrFail($id);
        $path = $slot === 1 ? $certification->attached_file : $certification->attached_file_2;
        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }
        $filename = $this->readableFilename($certification, $slot, $path);
        return Storage::disk('public')->response($path, $filename, [
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '%22', $filename).'"',
        ]);
    }

    /**
     * Genera un nombre de archivo legible: nombre de la certificación + sufijo si slot 2 + extensión.
     */
    private function readableFilename(Certification $certification, int $slot, string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf';
        $base = Str::slug($certification->name, '_');
        if (strlen($base) === 0) {
            $base = self::CERT_TYPES[$certification->type] ?? 'documento';
            $base = Str::slug($base, '_');
        }
        $suffix = $slot === 2 ? '_reverso' : '';
        return $base.$suffix.'.'.$extension;
    }
}
