@if ($doc->hasReverse())
    <a href="{{ route('conductores.documentos.ver', ['driver' => $driverId, 'document' => $doc->id, 'lado' => 'anverso']) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Ver anverso</a>
    <a href="{{ route('conductores.documentos.ver', ['driver' => $driverId, 'document' => $doc->id, 'lado' => 'reverso']) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Ver reverso</a>
    <a href="{{ route('conductores.documentos.descargar', ['driver' => $driverId, 'document' => $doc->id, 'lado' => 'anverso']) }}" class="text-gray-600 dark:text-gray-400 hover:underline">Descargar anverso</a>
    <a href="{{ route('conductores.documentos.descargar', ['driver' => $driverId, 'document' => $doc->id, 'lado' => 'reverso']) }}" class="text-gray-600 dark:text-gray-400 hover:underline">Descargar reverso</a>
@else
    <a href="{{ route('conductores.documentos.ver', ['driver' => $driverId, 'document' => $doc->id]) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Ver</a>
    <a href="{{ route('conductores.documentos.descargar', ['driver' => $driverId, 'document' => $doc->id]) }}" class="text-gray-600 dark:text-gray-400 hover:underline">Descargar</a>
@endif
