<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Documentos del conductor</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Sube imágenes o PDFs (carnet, licencia, certificado de antecedentes, etc.) y asigna un nombre legible a cada uno.</p>

    @if (session('documents_success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300 text-sm">
            {{ session('documents_success') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
            <div>
                <label for="doc_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Nombre del documento <span class="text-red-500">*</span>
                </label>
                <input type="text" id="doc_name" wire:model="name"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                    placeholder="ej. Carnet, Licencia, Certificado de antecedentes">
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="doc_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Archivo (imagen o PDF, máx. 10 MB) <span class="text-red-500">*</span>
                </label>
                <input type="file" id="doc_file" wire:model="file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                    class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 @error('file') border-red-500 @enderror">
                @error('file')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @if ($file)
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $file->getClientOriginalName() }}</p>
                @endif
            </div>
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150 text-sm">
            Subir documento
        </button>
    </form>

    @if ($documents->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400">Aún no hay documentos subidos.</p>
    @else
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach ($documents as $doc)
                <li class="py-3 flex items-center justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $doc->name }}</span>
                        @if ($doc->original_name)
                            <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">({{ $doc->original_name }})</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('conductores.documentos.ver', ['driver' => $driverId, 'document' => $doc->id]) }}"
                            target="_blank"
                            class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm">
                            Ver
                        </a>
                        <a href="{{ route('conductores.documentos.descargar', ['driver' => $driverId, 'document' => $doc->id]) }}"
                            class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300 text-sm">
                            Descargar
                        </a>
                        <button type="button"
                            wire:click="deleteDocument({{ $doc->id }})"
                            wire:confirm="¿Eliminar este documento?"
                            class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm">
                            Eliminar
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
