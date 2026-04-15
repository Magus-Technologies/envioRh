@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Sistema de Emisión Masiva RHE</h1>
            <p class="mt-2 text-lg text-gray-600">Gestiona tus Recibos por Honorarios Electrónicos de forma masiva</p>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-6 mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-blue-800">¿Cómo funciona?</h3>
                    <div class="mt-2 text-sm text-blue-700 space-y-2">
                        <p>1️⃣ <strong>Crea un lote</strong> para agrupar tus recibos del mes</p>
                        <p>2️⃣ <strong>Importa datos</strong> desde Excel/CSV o agrégalos manualmente</p>
                        <p>3️⃣ <strong>Genera el archivo</strong> en formato SUNAT (TXT o Excel)</p>
                        <p>4️⃣ <strong>Descarga y sube</strong> el archivo al portal de SUNAT con tu clave SOL</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Crear Lote -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Nuevo Lote</h3>
                            <a href="{{ route('lotes.create') }}" class="text-sm text-blue-600 hover:text-blue-800">Crear lote →</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Importar -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Importar Datos</h3>
                            <a href="{{ route('importacion.plantilla') }}" class="text-sm text-green-600 hover:text-green-800">Descargar plantilla →</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reportes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Ver Reportes</h3>
                            <a href="{{ route('reportes.plame') }}" class="text-sm text-purple-600 hover:text-purple-800">Generar reportes →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Lotes -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Lotes Recientes</h2>
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @php
                        $lotesRecientes = \App\Models\LoteEmision::latest()->take(5)->get();
                    @endphp
                    
                    @if($lotesRecientes->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recibos</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($lotesRecientes as $lote)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $lote->codigo_lote }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $lote->periodo_mes }}/{{ $lote->periodo_anio }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $lote->total_recibos }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $lote->estado === 'emitido' ? 'bg-green-100 text-green-800' : 
                                               ($lote->estado === 'generado' ? 'bg-blue-100 text-blue-800' : 
                                               ($lote->estado === 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                            {{ ucfirst($lote->estado) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <a href="{{ route('lotes.show', $lote->id) }}" class="text-blue-600 hover:text-blue-800">Ver</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500 text-center py-8">No hay lotes creados aún. <a href="{{ route('lotes.create') }}" class="text-blue-600 hover:text-blue-800">Crea tu primer lote</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
