@extends('layouts.app')

@section('title', 'Calendario de mantenimientos')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Calendario de mantenimientos</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Mantenimientos programados y en proceso por fecha</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('mantenimientos.calendario', ['year' => $prev->year, 'month' => $prev->month]) }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                <i class="fas fa-chevron-left mr-2"></i> {{ $prev->locale('es')->translatedFormat('F Y') }}
            </a>
            <span class="text-lg font-semibold text-gray-900 dark:text-white px-2">{{ $start->locale('es')->translatedFormat('F Y') }}</span>
            @if($canNext)
            <a href="{{ route('mantenimientos.calendario', ['year' => $next->year, 'month' => $next->month]) }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                {{ $next->locale('es')->translatedFormat('F Y') }} <i class="fas fa-chevron-right ml-2"></i>
            </a>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 overflow-x-auto">
            <table class="w-full border-collapse" style="min-width: 600px;">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2 px-2 text-sm font-semibold text-gray-700 dark:text-gray-300 w-24">Lun</th>
                        <th class="text-left py-2 px-2 text-sm font-semibold text-gray-700 dark:text-gray-300 w-24">Mar</th>
                        <th class="text-left py-2 px-2 text-sm font-semibold text-gray-700 dark:text-gray-300 w-24">Mié</th>
                        <th class="text-left py-2 px-2 text-sm font-semibold text-gray-700 dark:text-gray-300 w-24">Jue</th>
                        <th class="text-left py-2 px-2 text-sm font-semibold text-gray-700 dark:text-gray-300 w-24">Vie</th>
                        <th class="text-left py-2 px-2 text-sm font-semibold text-gray-700 dark:text-gray-300 w-24">Sáb</th>
                        <th class="text-left py-2 px-2 text-sm font-semibold text-gray-700 dark:text-gray-300 w-24">Dom</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $first = $start->copy()->startOfMonth();
                        $pad = ($first->dayOfWeek + 6) % 7;
                        $daysInMonth = $first->daysInMonth;
                        $weeks = (int) ceil(($pad + $daysInMonth) / 7);
                    @endphp
                    @for ($w = 0; $w < $weeks; $w++)
                    <tr class="border-b border-gray-100 dark:border-gray-700">
                        @for ($d = 0; $d < 7; $d++)
                        @php
                            $cellIndex = $w * 7 + $d;
                            $dayNum = $cellIndex - $pad + 1;
                            $isEmpty = $cellIndex < $pad || $dayNum > $daysInMonth;
                        @endphp
                        <td class="align-top py-2 px-2 border-r border-gray-100 dark:border-gray-700 last:border-r-0 min-h-[100px]">
                            @if ($isEmpty)
                                <span class="text-gray-300 dark:text-gray-600 text-sm">—</span>
                            @else
                                @php
                                    $dateStr = sprintf('%04d-%02d-%02d', $start->year, $start->month, $dayNum);
                                    $events = $byDate->get($dateStr, collect());
                                @endphp
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $dayNum }}</div>
                                <div class="mt-1 space-y-1">
                                    @foreach($events as $m)
                                    <a href="{{ route('mantenimientos.show', $m->id) }}" class="block text-xs rounded px-2 py-1 truncate {{ $m->status === 'in_progress' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' }}" title="{{ $m->vehicle->license_plate ?? '' }} - {{ $m->work_description }}">
                                        {{ $m->vehicle->license_plate ?? 'N/A' }}: {{ Str::limit($m->work_description, 20) }}
                                    </a>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        @endfor
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
